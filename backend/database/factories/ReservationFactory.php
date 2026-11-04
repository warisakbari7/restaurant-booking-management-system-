<?php

namespace Database\Factories;

use App\Models\Reservation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

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
        return [
            'user_id' => $userId,
            'reservation_date' => fake()->date(),
            'reservation_time' => fake()->time(),
            'guest_count' => fake()->numberBetween(1,8),
            'status' => 'Pending'
        ];
    }
}
