<?php

namespace Tests\Feature;

use Database\Factories\UserFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;
use App\Models\User;
use App\Models\Reservation;

class ReservationApiTest extends TestCase
{
    use RefreshDatabase;
    protected $seed = true;
    /**
     * A basic feature test example.
     */
    public function test_unauthenticated_user_cannot_create_reservation(): void
    {
        $response = $this->postJson('/api/reservations', 
        [
            'reservation_date' => '2026-11-10',
            'reservation_time' => '14:30:00',
            'guest_count' => 5,
            'special_requests' => null

        ]);

        $response->assertUnauthorized();
    }

    public function test_authenticated_user_can_create_reservation() : void
    {
            $user = User::factory()->create();
            Sanctum::actingAs($user);
            $response = $this->postJson('/api/reservations',
            [
                'reservation_date' => '2026-11-10',
                'reservation_time' => '14:30:00',
                'guest_count' => 5,
                'special_requests' => 'Window seat preferred'
            ]);
            $response->assertCreated();

            $response->assertJsonPath('reservation.user_id', $user->id);
            $response->assertJsonPath('reservation.guest_count', 5);
            $response->assertJsonPath('reservation.status', 'Pending');
            $this->assertDatabaseHas('reservations',
            [
                'user_id' => $user->id,
                'reservation_date' => '2026-11-10',
                'reservation_time' => '14:30:00',
                'guest_count' => 5,
                'special_requests' => 'Window seat preferred'
            ]);
    }

    public function test_reservation_creation_assigns_suitable_tables() : void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $response = $this->postJson('/api/reservations',
        [
            'reservation_date' => '2026-11-11',
            'reservation_time' => '13:00:00',
            'guest_count' => 5,
        ]);
        $response->assertCreated();
        $response->assertJsonPath('reservation.user_id', $user->id);
        $response->assertJsonPath('reservation.status', 'Pending');
        $response->assertJsonPath('reservation.guest_count', 5);
        $this->assertDatabaseHas('reservations',
        [
            'user_id' => $user->id,
            'reservation_date' => '2026-11-11',
            'reservation_time' => '13:00:00',
            'guest_count' => 5,
        ]);
        $tables = $response->json('tables');
        $this->assertNotEmpty($tables, 'At least one table should be assigned.');

        $totalCapacity = collect($tables)->sum('capacity');
        $this->assertGreaterThanOrEqual(5, $totalCapacity, 'Combined capacity must be at least 5.');
        $this->assertTrue(
            collect($tables)->every(fn ($table) => $table['status'] !== 'Out of Service' ),
            'Assigned table must not have Out of Service status.'
        );

        $reservationId = $response->json('reservation.id');
        $reservation = Reservation::with('tables')->findOrFail($reservationId);
        $this->assertEqualsCanonicalizing(
            collect($tables)->pluck('id')->all(),
            $reservation->tables->pluck('id')->all()
        );

    }

    public function test_overlapping_reservations_are_not_assigned_the_same_tables() : void
    {
                $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        Sanctum::actingAs($user1);

        $response1 = $this->postJson('/api/reservations',
        [
            'reservation_date' => '2026-11-12',
            'reservation_time' => '13:00:00',
            'guest_count' => 5
        ]);

        Sanctum::actingAs($user2);
        $response2 = $this->postJson('/api/reservations',
        [
            'reservation_date' => '2026-11-12',
            'reservation_time' => '14:00:00',
            'guest_count' => 5
        ]);

        $response1->assertCreated();
        $response2->assertCreated();
        
        $tables1 = collect($response1->json('tables'))->pluck('id')->all();
        $tables2 = collect($response2->json('tables'))->pluck('id')->all();
        $overlappingTables = array_intersect($tables1, $tables2);
        $this->assertEmpty($overlappingTables, 'Overlapping reservations must not share tables.');
    }


}
