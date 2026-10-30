<?php
    namespace App\Services;

    use App\Models\Reservation;
    use Carbon\Carbon;
    use App\Models\RestaurantTable;
    use Illuminate\Support\Facades\DB;
    use RuntimeException;
    use InvalidArgumentException;

    class ReservationService
    {
        /**
         * Create a new class instance.
         */
        public function __construct()
        {
            //
        }

        public function createReservation(int $id, string $date, string $time, int $guestCount, ?string $specialRequests = null)
        {
            if($guestCount <= 0 || $guestCount > config('restaurant.rules.max_guests'))
                throw new RuntimeException('Invalid Guest Amount!');

            if(!Carbon::hasFormat($date,"Y-m-d") || !Carbon::hasFormat($time,"H:i:s"))
                throw new InvalidArgumentException('Date must use Y-m-d format and time must use H:i:s format.');
            $parseDate = Carbon::createFromFormat('!Y-m-d',$date);
            if($parseDate->format('Y-m-d') !== $date)
                {
                    throw new InvalidArgumentException(
                        'The reservation date is not a valid calendar date.'
                    );
                }
            $dateTime = Carbon::parse("$date $time");
            if($dateTime->isPast())
                throw new InvalidArgumentException("The reservation date and time must be in future!");
            if(!Carbon::hasFormat($dateTime,"Y-m-d H:i:s"))
                throw new InvalidArgumentException('The date and time must be in followin format Year-Month-day Hour:Minute:Second');

            $reservation = DB::transaction(function()use($id, $date, $time, $guestCount,$specialRequests)
            {
                $lockedTables = RestaurantTable::query()->orderBy('id')->lockForUpdate()->get();
                $tables = $this->selectTablesForReservation($date, $time, $guestCount);
                if($tables === null)
                    throw new RuntimeException('No suitable table found.');

                $reservation = Reservation::create([
                        'user_id' => $id,
                        'reservation_date' => $date,
                        'reservation_time' => $time,
                        'guest_count' => $guestCount,
                        'special_requests' => $specialRequests,
                        'status' => 'Pending'
                    ]);
                $reservation->tables()->attach($tables->pluck('id')->all());
                return $reservation;

            });
            return $reservation;
        }

        public function selectTablesForReservation(string $date, string $time, int $guestCount)
        {
            $tables = $this->findAvailableTables($date, $time, $guestCount);
            $tables =  $this->setTables($tables, $guestCount);
            if($tables == null)
                return null;

            return $tables;

        }


        public function findAvailableTables(string $date, string $time, int $guestCount)
        {

            ['startTime' => $startTime,
            'endTime' => $endTime,
            'blockedUntil' => $blockedUntil]= $this->calculateReservationTime($time, $date);

            $duration = config('restaurant.rules.reservation_duration_minutes');
            $duration += config('restaurant.rules.turnover_minutes');

            $tables = RestaurantTable::whereDoesntHave('reservations',function($query) use ($startTime, $blockedUntil,$duration){
                $query->whereNotIn('status',['Cancelled','No Show'])
                ->whereRaw('TIMESTAMP(reservations.reservation_date, reservations.reservation_time) < ?',[$blockedUntil])
                ->whereRaw("? < TIMESTAMPADD(MINUTE,?,TIMESTAMP(reservations.reservation_date, reservations.reservation_time))",[$startTime,$duration]);
                })->where('status','!=','Out of Service')->get();
                return $tables;
        }

        public function calculateReservationTime(string $startTime, string $date)
        {
            $duration = config('restaurant.rules.reservation_duration_minutes');
            $turnover = config('restaurant.rules.turnover_minutes');
            $startTime = Carbon::parse($date.' '.$startTime);
            $endTime = $startTime->copy()->addMinutes($duration);
            $blockedUntil = $endTime->copy()->addMinutes($turnover);

            return ['startTime' => $startTime,
            'endTime' =>  $endTime,
            'blockedUntil' =>  $blockedUntil];
        }

        public function setTables($tables, int $guestCount)
        {

            $table = $tables->where('capacity','>=',$guestCount)->sortBy([
                ['capacity','asc'],
                ['table_number','asc']
            ])->take(1);


            if($table->isEmpty())
                {
                    $tableCombination = $this->findCombination($guestCount, 0, collect(), collect(), $tables, 0);
                    if($tableCombination->isEmpty())
                        return null;

                    return $tableCombination;
                }
            else
                {
                    return $table;
                }


        }

        public function findCombination(int $guestCount, int $currentCapacity, $selectedTables, $bestCombination, $tables, int $startIndex)
        {
            if ($currentCapacity >= $guestCount)
                {
                    if(
                        $bestCombination->isEmpty() ||
                        $bestCombination->count() > $selectedTables->count() ||
                    (
                        $bestCombination->count() === $selectedTables->count() &&
                        $bestCombination->sum('capacity') > $currentCapacity ||
                        (
                            $bestCombination->count() === $selectedTables->count() &&
                            $bestCombination->sum('capacity') == $currentCapacity &&
                            $this->compareTableNumbers($selectedTables, $bestCombination) == -1
                        )


                    ))
                    {
                        $bestCombination = $selectedTables;
                    }

                    return $bestCombination;
                }

                for($i = $startIndex; $i < $tables->count(); $i++)
                    {
                        $table = $tables->values()->get($i);
                        $tablesCombination = clone $selectedTables;
                        $tablesCombination->push($table);
                        $capacity = $currentCapacity;
                        $capacity += $table->capacity;

                        $bestCombination = $this->findCombination($guestCount, $capacity, $tablesCombination, $bestCombination, $tables, $i + 1);
                    }
                return $bestCombination;

        }

        public function compareTableNumbers($firstCombination, $secondCombination)
        {
            $firstCombination = $firstCombination->sortBy('table_number')->values();
            $secondCombination = $secondCombination->sortBy('table_number')->values();

            for($i = 0; $i < $firstCombination->count(); $i++ )
                {
                    $result = $firstCombination->get($i)->table_number <=> $secondCombination->get($i)->table_number;
                    if($result != 0)
                        return $result;
                }
            return 0;
        }

        public function cancelReservation(int $userId, int $reservationId)
        {
            return DB::transaction(function() use($userId, $reservationId)
            {
                $reservation = Reservation::where('id', $reservationId)
                ->where('user_id', $userId)->lockForUpdate()->firstOrFail();
                if(!in_array($reservation->status, ['Pending', 'Confirmed'], true))
                    {
                        return null;
                    }
                
                $deadlineCancellation = config('restaurant.rules.cancellation_deadline_minutes');
                        
                $reservationTime = Carbon::parse($reservation->reservation_date . ' ' . $reservation->reservation_time);
                $cancellationDeadline = $reservationTime->copy()
                    ->subMinutes($deadlineCancellation);
                if(now()->greaterThan($cancellationDeadline))
                    {
                       return null;
                    }
                $reservation->status = 'Cancelled';
                $reservation->save();
                $reservation->load('tables');
                                
                return $reservation;
            });
        }

    }
