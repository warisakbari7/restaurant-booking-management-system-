<?php

namespace Database\Factories;

use App\Models\Reservation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends Factory<Reservation>
 */
class ReservationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $userId = User::first()->id;
        $dateTime = now()->addDay()->addHours(2);
        return [
            'user_id' => $userId,
            'reservation_date' => $dateTime->toDateString(),
            'reservation_time' => $dateTime->format('H:i:s'),
            'guest_count' => fake()->numberBetween(1,8),
            'status' => 'Pending'
        ];
    }
}
