<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;
use App\Models\User;
use App\Models\Reservation;
use App\Models\RestaurantTable;
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
        $dateTime = Carbon::now()->addDays(1);
        $response = $this->postJson('/api/reservations', 
        [
            'reservation_date' => $dateTime->toDateString(),
            'reservation_time' => $dateTime->format('H:i:s'),
            'guest_count' => 5,
            'special_requests' => null

        ]);

        $response->assertUnauthorized();
    }

    public function test_authenticated_user_can_create_reservation() : void
    {
            $user = User::factory()->create();
            Sanctum::actingAs($user);
            $dateTime = Carbon::now()->addDays(1);
            $response = $this->postJson('/api/reservations',
            [
            'reservation_date' => $dateTime->toDateString(),
            'reservation_time' => $dateTime->format('H:i:s'),
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
                'reservation_date' => $response->json('reservation.reservation_date'),
                'reservation_time' => $response->json('reservation.reservation_time'),
                'guest_count' => 5,
                'special_requests' => 'Window seat preferred'
            ]);
    }

    public function test_reservation_creation_assigns_suitable_tables() : void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $dateTime = Carbon::now()->addDays(1);
        $response = $this->postJson('/api/reservations',
        [
            'reservation_date' => $dateTime->toDateString(),
            'reservation_time' => $dateTime->format('H:i:s'),
            'guest_count' => 5,
        ]);
        $response->assertCreated();
        $response->assertJsonPath('reservation.user_id', $user->id);
        $response->assertJsonPath('reservation.status', 'Pending');
        $response->assertJsonPath('reservation.guest_count', 5);
        $this->assertDatabaseHas('reservations',
        [
            'user_id' => $user->id,
            'reservation_date' => $response->json('reservation.reservation_date'),
            'reservation_time' => $response->json('reservation.reservation_time'),
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
        $dateTime = Carbon::now()->addDays(1);

        $response1 = $this->postJson('/api/reservations',
        [
            'reservation_date' => $dateTime->toDateString(),
            'reservation_time' => $dateTime->format('H:i:s'),
            'guest_count' => 5
        ]);

        Sanctum::actingAs($user2);
        $dateTime->addHours(1);
        $response2 = $this->postJson('/api/reservations',
        [
            'reservation_date' => $dateTime->toDateString(),
            'reservation_time' => $dateTime->format('H:i:s'),
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
        $dateTime = Carbon::now()->addDays(1);
        $response1 = $this->postJson('/api/reservations',
        [
            'reservation_date' => $dateTime->toDateString(),
            'reservation_time' => $dateTime->format('H:i:s'),
            'guest_count' => 3,
        ]);
        $response1->assertCreated();
        $dateTime->addMinutes(135);

        $response2 = $this->postJson('/api/reservations',
        [
            'reservation_date' => $dateTime->toDateString(),
            'reservation_time' => $dateTime->format('H:i:s'),
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
        $dateTime = Carbon::now()->addDays(1);
        $response = $this->postJson('/api/reservations',
        [
            'reservation_date' => $dateTime->toDateString(),
            'reservation_time' => $dateTime->format('H:i:s'),
            'guest_count' => 2,
            'special_requests' => 'Window seat please.'
        ]);
        $response->assertCreated();


        $dateTime->addDays(1);
        $reservationId = $response->json('reservation.id');
        $response = $this->patchJson("/api/reservations/{$reservationId}",
        [
            'reservation_date' => $dateTime->toDateString(),
            'reservation_time' => $dateTime->format('H:i:s'),
            'guest_count' => 3,
            'special_requests' => 'First floor preferred.'
        ]);
        $response->assertOK();

        $response->assertJsonPath('reservation.reservation_date',$dateTime->toDateString());
        $response->assertJsonPath('reservation.reservation_time', $dateTime->format('H:i:s'));
        $response->assertJsonPath('reservation.guest_count', 3);
        $response->assertJsonPath('reservation.special_requests','First floor preferred.');
        $response->assertJsonPath('reservation.status', 'Pending');

        $this->assertDatabaseHas('reservations', 
        [
            'user_id' => $user->id,
            'reservation_date' => $response->json('reservation.reservation_date'),
            'reservation_time' => $response->json('reservation.reservation_time'),
            'guest_count' => 3,
            'special_requests' => 'First floor preferred.'
        ]);

    }

    public function test_authenticated_user_cannot_update_with_invalid_data() : void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $dateTime = Carbon::now()->addDays(1);
        $response = $this->postJson('/api/reservations',
        [
            'reservation_date' => $dateTime->toDateString(),
            'reservation_time' => $dateTime->format('H:i:s'),
            'guest_count' => 2,
            'special_requests' => 'Well decorated table.'
        ]);
        $response->assertCreated();
        $reservationId = $response->json('reservation.id');
        $dateTime->subHours(1);
        $response = $this->patchJson("/api/reservations/{$reservationId}",
        [
            'reservation_date' => $dateTime->toDateString(),
            'reservation_time' => $dateTime->format('H:i:s'),
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
        $dateTime = Carbon::now()->addDays(1);

        $response = $this->patchJson("/api/reservations/{$reservation->id}",
        [
            'reservation_date' => $dateTime->toDateString(),
            'reservation_time' => $dateTime->format('H:i:s'),
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
        $dateTime->addDays(1);
        $response = $this->patchJson("/api/reservations/{$reservation->id}",
        [
            'reservation_date' => $dateTime->toDateString(),
            'reservation_time' => $dateTime->format('H:i:s'),
            'guest_count' => 1
        ]);

        $response->assertStatus(409);

    }

    public function test_authenticated_user_can_cancel_the_reservation() : void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $dateTime = Carbon::now()->addDays(1);
        $response = $this->postJson('/api/reservations',
        [
            'reservation_date' => $dateTime->toDateString(),
            'reservation_time' => $dateTime->format('H:i:s'),
            'guest_count' => 5
        ]);
        $response->assertCreated();
        $reservationId = $response->json('reservation.id');
        $response = $this->patchJson("/api/reservations/{$reservationId}/cancel");
        $response->assertOk();
        $response->assertJsonPath('reservation.status', 'Cancelled');
        $this->assertDatabaseHas('reservations',
        [
            'id' => $reservationId,
            'status' => 'Cancelled'
        ]);
    }

    public function test_a_reservation_cannot_be_cancelled_after_deadline_time() : void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $dateTime = Carbon::now()->subDays(1);
        $reservation = Reservation::factory()->create(
            [
                'user_id' => $user->id,
                'reservation_date' => $dateTime->toDateString(),
                'reservation_time' => $dateTime->format('H:i:s'),
                'guest_count' => 3,
                'status' => 'Pending'
            ]
        );
        $response = $this->patchJson("/api/reservations/{$reservation->id}/cancel");
        $response->assertStatus(409 );
        $this->assertDatabaseHas('reservations',[
            'id' => $reservation->id,
            'status' => 'Pending'
        ]);
    }

    public function test_a_user_cannot_cancel_another_users_reservation() : void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $dateTime = Carbon::now()->addDays(1);
        $reservation = Reservation::factory()->create(
        [  
            'user_id' => $user1->id,
            'reservation_date' => $dateTime->toDateString(),
            'reservation_time' => $dateTime->format('H:i:s'),
            'guest_count' => 1,
            'status' => 'Pending'
        ]);
        Sanctum::actingAs($user2);
        $response = $this->patchJson("/api/reservations/{$reservation->id}/cancel");
        $response->assertStatus(404);
        $this->assertDatabaseHas('reservations',
        [
            'user_id' => $user1->id,
            'id' => $reservation->id,
            'status' => 'Pending'
        ]);
    }

    public function test_unauthenticated_user_cannot_cancel_reservations() : void
    {
        $user = User::factory()->create();
        $dateTime = Carbon::now()->addDays(1);
        $reservation = Reservation::factory()->create(
            [
                'user_id' => $user->id,
                'reservation_date' => $dateTime->toDateString(),
                'reservation_time' => $dateTime->format('H:i:s'),
                'guest_count' => 4,
                'status' => 'Pending'
            ]
        );

        $response = $this->patchJson("/api/reservations/{$reservation->id}/cancel");
        $response->assertUnauthorized();
        $this->assertDatabaseHas('reservations',
        [
            'user_id' => $user->id,
            'id' => $reservation->id,
            'reservation_date' => $dateTime->toDateString(),
            'reservation_time' => $dateTime->format('H:i:s'),
            'guest_count' => 4,
            'status' => 'Pending'
        ]);
    }

    public function test_authenticated_user_sees_only_their_reservation() : void
    {
        $dateTime = Carbon::now()->addDays(1);
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $reservation1 = Reservation::factory()->create(
            [
                'user_id' => $user1->id,
                'reservation_date' => $dateTime->toDateString(),
                'reservation_time' => $dateTime->format('H:i:s'),
                'guest_count' => 3,
                'status' => 'Pending'
            ]
        );
        $reservation2 = Reservation::factory()->create(
            [
                'user_id' => $user2->id,
                'reservation_date' => $dateTime->toDateString(),
                'reservation_time' => $dateTime->format('H:i:s'),
                'guest_count' => 3,
                'status' => 'Pending'
            ]
        );
        Sanctum::actingAs($user1);
        $response = $this->getJson('/api/reservations');
        $response->assertStatus(200);
        $reservations = $response->json('reservations.data');
        $returnedIds = collect($reservations)->pluck('id')->all();
        $this->assertContains($reservation1->id, $returnedIds);
        $this->assertNotContains($reservation2->id, $returnedIds);
        foreach($reservations as $reservation)
            {
                $this->assertEquals($user1->id, $reservation['user_id']);
            }

    }

    public function test_unauthenticated_user_cannot_list_reservations() : void
    {
        $user = User::factory()->create();
        $dateTime = Carbon::now()->addDays(1);
        Reservation::factory()->create(
            [
                'user_id' => $user->id,
                'reservation_date' => $dateTime->toDateString(),
                'reservation_time' => $dateTime->format('H:i:s'),
                'guest_count' => 2,
            ]
        );
         $response = $this->getJson('/api/reservations');
         $response->assertUnauthorized();
    }

    public function test_user_can_view_thier_reservaton_details() : void
    {
        $user = User::factory()->create();
        $dateTime = Carbon::now()->addDays(1);
        Sanctum::actingAs($user);
        $reservation = Reservation::factory()->create(
            [
                'user_id' => $user->id,
                'reservation_date' => $dateTime->toDateString(),
                'reservation_time' => $dateTime->format('H:i:s'),
                'guest_count' => 5
            ]
        );

        $response = $this->getJson("/api/reservations/{$reservation->id}");
        $response->assertOk();
        $response->assertJsonPath('reservation.user_id', $user->id);
        $response->assertJsonPath('reservation.id', $reservation->id);
        $response->assertJsonPath('reservation.reservation_date', $reservation->reservation_date);
        $response->assertJsonPath('reservation.reservation_time', $reservation->reservation_time);
        $response->assertJsonPath('reservation.guest_count', $reservation->guest_count);
        $response->assertJsonPath('reservation.status', 'Pending');


    }

    public function test_user_cannot_view_others_reservation_details() : void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $dateTime = Carbon::now()->addDays(1);
        $reservation1 = Reservation::factory()->create(
            [
                'user_id' => $user1->id,
                'reservation_date' => $dateTime->toDateString(),
                'reservation_time' => $dateTime->format('H:i:s'),
                'guest_count' => 3,
            ]
        );
     
        Sanctum::actingAs($user2);
        $response= $this->getJson("/api/reservations/{$reservation1->id}");
        $response->assertNotFound();
    }

    public function test_unauthenticated_user_cannot_view_details_of_reservation() : void
    {
        $user = User::factory()->create();
        $dateTime = Carbon::now()->addDay();
        $reservation = Reservation::factory()->create(
            [
                'user_id' => $user->id,
                'reservation_date' => $dateTime->toDateString(),
                'reservation_time' => $dateTime->format('H:i:s'),
                'guest_count' => 3
            ]
        );

        $response = $this->getJson("/api/reservations/{$reservation->id}");
        $response->assertUnauthorized();
    }

    public function test_a_reservation_cannot_be_created_when_tables_are_unavailable() : void
    {

        $this->assertDatabaseHas('restaurant_tables', [
            'status' => 'Available',
        ]);
        RestaurantTable::where('status', 'Available')->update(
            [
                'status' => 'Out of Service'
            ]
        );
        $dateTime = Carbon::now()->addDay();
        $user = User::factory()->create();
        Sanctum::actingAS($user);
        $reservationCount = Reservation::count();
        
        $response = $this->postJson('/api/reservations',
        [
            'reservation_date' => $dateTime->toDateString(),
            'reservation_time' => $dateTime->format('H:i:s'),
            'guest_count' => 3
        ]);
        $response->assertStatus(409);
        $this->assertEquals($reservationCount, Reservation::count());
        $response->assertJson(
            [
                'message' => 'No suitable table found.'
            ]
        );
       
    }

    public function test_a_reservation_cannot_be_created_for_more_than_8_guests() : void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $dateTime = Carbon::now()->addDay();
        $reservationCount = Reservation::count();
        $response = $this->postJson('/api/reservations',
        [
            'reservation_date' => $dateTime->toDateString(),
            'reservation_time' => $dateTime->format('H:i:s'),
            'guest_count' => 9,
        ]);
        $response->assertUnprocessable();
        $this->assertEquals($reservationCount, Reservation::count());
    }

    public function test_a_reservation_cannot_be_created_with_zero_guest_count() : void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $dateTime = Carbon::now()->addDay();
        $reservationCount = Reservation::count();
        $response = $this->postJson('/api/reservations',
        [
            'reservation_date' => $dateTime->toDateString(),
            'reservation_time' => $dateTime->format('H:i:s'),
            'guest_count' => 0
        ]);
        $response->assertUnprocessable();
        $this->assertEquals($reservationCount, Reservation::count());

    }

    public function test_a_reservation_cannot_be_created_with_invalid_date() : void
    {
        $user = User::factory()->create();
        $dateTime = Carbon::now()->subDay();
        Sanctum::actingAs($user);
        $reservationCount = Reservation::count();
        $response = $this->postJson('/api/reservations',
        [
            'reservation_date' => $dateTime->toDateString(),
            'reservation_time' => $dateTime->format('H:i:s'),
            'guest_count' => 5
        ]);
        $response->assertUnprocessable();
        $this->assertEquals($reservationCount, Reservation::count());
    }

    public function test_a_reservation_creation_will_be_rejected_with_requested_time_in_past() : void
    {
        $user = User::factory()->create();
        $dateTime = Carbon::now()->subHour();
        Sanctum::actingAs($user);
        $reservationCount = Reservation::count();
        $response = $this->postJson('/api/reservations',
        [
            'reservation_date' => $dateTime->toDateString(),
            'reservation_time' => $dateTime->format('H:i:s'),
            'guest_count' => 5
        ]);
        $response->assertUnprocessable();
        $this->assertEquals($reservationCount, Reservation::count());
    }
}