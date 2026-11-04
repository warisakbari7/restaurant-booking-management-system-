<?php

namespace Tests\Feature;

use Database\Factories\UserFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;
use App\Models\User;
use App\Models\Reservation;
use Illuminate\Support\Carbon;

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

    public function test_turnover_boundary_reservation() : void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response1 = $this->postJson('/api/reservations',
        [
            'reservation_date' => '2026-11-13',
            'reservation_time' => '13:00:00',
            'guest_count' => 3,
        ]);
        $response1->assertCreated();
        $response2 = $this->postJson('/api/reservations',
        [
            'reservation_date' => '2026-11-13',
            'reservation_time' => '15:15:00',
            'guest_count' => 3,
        ]);
        $response2->assertCreated();
        $tables1 = collect($response1->json('tables'))->pluck('id')->all();
        $tables2 = collect($response2->json('tables'))->pluck('id')->all();
        sort($tables1);
        sort($tables2);
        $this->assertSame($tables1, $tables2);
    }

    public function test_authenticated_user_can_update_the_reservation() : void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $response = $this->postJson('/api/reservations',
        [
            'reservation_date' => '2026-11-12',
            'reservation_time' => '12:00:00',
            'guest_count' => 2,
            'special_requests' => 'Window seat please.'
        ]);
        $response->assertCreated();



        $reservationId = $response->json('reservation.id');
        $response = $this->patchJson("/api/reservations/{$reservationId}",
        [
            'reservation_date' => '2026-11-13',
            'reservation_time' => '11:00:00',
            'guest_count' => 3,
            'special_requests' => 'First floor preferred.'
        ]);
        $response->assertOK();

        $response->assertJsonPath('reservation.reservation_date','2026-11-13');
        $response->assertJsonPath('reservation.reservation_time', '11:00:00');
        $response->assertJsonPath('reservation.guest_count', 3);
        $response->assertJsonPath('reservation.special_requests','First floor preferred.');
        $response->assertJsonPath('reservation.status', 'Pending');

        $this->assertDatabaseHas('reservations', 
        [
            'user_id' => $user->id,
            'reservation_date' => '2026-11-13',
            'reservation_time' => '11:00:00',
            'guest_count' => 3,
            'special_requests' => 'First floor preferred.'
        ]);

    }

    public function test_authenticated_user_cannot_update_with_invalid_data() : void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $response = $this->postJson('/api/reservations',
        [
            'reservation_date' => '2026-11-13',
            'reservation_time' => '10:00:00',
            'guest_count' => 2,
            'special_requests' => 'Well decorated table.'
        ]);
        $response->assertCreated();
        $reservationId = $response->json('reservation.id');
        $response = $this->patchJson("/api/reservations/{$reservationId}",
        [
            'reservation_date' => '11-2026-13',
            'reservation_time' => '09-00-00',
            'guest_count' => 0,
        ]);
        $response->assertUnprocessable();
    }

    public function test_a_user_cannot_update_others_record() : void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $reservation = Reservation::factory()->create(
            [
                'user_id' => $user1->id
            ]
        );
        Sanctum::actingAs($user2);

        $response = $this->patchJson("/api/reservations/{$reservation->id}",
        [
            'reservation_date' => '2026-11-23',
            'reservation_time' => '10:00:00',
            'guest_count' => 3
        ]);
        $response->assertNotFound();

        $this->assertDatabaseHas('reservations', 
        [
            'id' => $reservation->id,
            'user_id' => $user1->id
        ]);
    }

    public function test_reservation_cannot_be_modified_after_the_deadline_time() : void
    {
        $user = User::factory()->create();
        $dateTime = Carbon::now()->subHours(3);
        $reservation = Reservation::factory()->create(
            [
                'user_id' => $user->id,
                'reservation_date' => $dateTime->toDateString(),
                'reservation_time' => $dateTime->format('H:i:s'),
                'guest_count' => 2
            ]
        );
        Sanctum::actingAs($user);
        $response = $this->patchJson("/api/reservations/{$reservation->id}",
        [
            'reservation_date' => '2026-11-10',
            'reservation_time' => '20:00:00',
            'guest_count' => 1
        ]);

        $response->assertStatus(409);

    }


}
