<?php
    namespace App\Services;
    use Carbon\Carbon;
    use App\Models\RestaurantTable;


    class ReservationService
    {
        /**
         * Create a new class instance.
         */
        public function __construct()
        {
            //
        }

        public function calculateReservationTime($startTime)
        {
            $duration = config('restaurant.rules.reservation_duration_minutes');
            $turnover = config('restaurant.rules.turnover_minutes');    
            $startTime = Carbon::parse($startTime);
            $endTime = $startTime->copy()->addMinutes($duration);
            $blockedUntil = $endTime->copy()->addMinutes($turnover);
            return ['startTime' => $startTime,
            'endTime' =>  $endTime,
            'blockedUntil' =>  $blockedUntil];
        }
        public function findAvailableTables($date, $time, $guestCount)
        {

            ['startTime' => $startTime, 
            'endTime' => $endTime,
            'blockedUntil' => $blockedUntil]= $this->calculateReservationTime($time);

            $tables = RestaurantTable::where('status','!=','Out of Service')
            ->where('capacity','>=', $guestCount)->get();
        }
    }
