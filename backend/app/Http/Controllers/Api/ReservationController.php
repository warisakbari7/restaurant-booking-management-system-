<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\StoreReservationRequest;
use App\Services\ReservationService;

class ReservationController extends Controller
{
    public function store(StoreReservationRequest $request, ReservationService $reservationService)
    {
        $validated = $request->validated();
        try {
            $reservation = $reservationService->createReservation(
                $request->user()->id,
                $validated['reservation_date'],
                $validated['reservation_time'],
                $validated['guest_count'],
                $validated['special_requests'] ?? null
            );
            $reservation->load('tables');
            return response()->json([
                'message' => 'Reservation created successfully.',
                'tables' => $reservation->tables,
                'reservation' => $reservation
            ], 201);

        } catch (\InvalidArgumentException $e) {
            return  response()->json(
                ['message' => $e->getMessage()], 422
            );
        }
        
        catch(\RuntimeException $re)
        {
            return response()->json(
                ['message' => $re->getMessage()], 409
            );
        }
    }
}
