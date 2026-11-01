<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\StoreReservationRequest;
use App\Services\ReservationService;
use App\Models\Reservation;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Http\Requests\UpdateReservationRequest;
use PhpParser\Node\Stmt\TryCatch;

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

    public function index(Request $request)
    {
        $userId = $request->user()->id;
        $reservations = Reservation::with('tables')->where('user_id', $userId)
        ->orderByDesc('reservation_date')
        ->orderByDesc('reservation_time')->paginate(10);
        return response()->json([
            'reservations' => $reservations,
            ],200);
    }

    public function show(Request $request, int $reservationId)
    {
       $reservation = Reservation::with('tables')
       ->where('user_id',$request->user()->id)
       ->findOrFail($reservationId);

       return response()->json(
        [
            'reservation' => $reservation,
            'tables' => $reservation->tables,
        ], 200
       );

    }

    public function cancel(Request $request, int $reservationId, ReservationService $reservationService)
    {
        $userId = $request->user()->id;
        $updatedReservation = $reservationService->cancelReservation($userId, $reservationId);
        if($updatedReservation !== null)
            {
                return response()->json(
                    [
                        'success' => true,
                        'message' => 'Reservation cancelled successfully.',
                        'reservation' => $updatedReservation,
                        'tables' => $updatedReservation->tables
                    ], 200
                );
            }
        return response()->json(
            [
                'success' => false,
                'message' => 'This reservation cannot be cancelled.'
            ], 409
        );
    }

    public function update(UpdateReservationRequest $request, $reservationId, ReservationService $service)
    {
        $validated = $request->validated();
        try
        {
            $reservation = $service->updateReservation(
                $request->user()->id,
                $reservationId,
                $validated['reservation_date'],
                $validated['reservation_time'],
                $validated['guest_count'],
                $validated['special_requests'] ?? null
            );
            if($reservation === null)
                return response()->json(['message' => 'This reservation can no longer be modified.'], 409);
            $reservation->load('tables');
            return response()->json(
                [
                    'message' => 'Reservation updated successfully',
                    'reservation' => $reservation,
                    'tables' => $reservation->tables
                ], 200
            );
        }
        catch( \Illuminate\Database\Eloquent\ModelNotFoundException $e)
        {
            return response()->json(
                [
                    'message' => 'Reservation not found.'
                ], 404
            );
        }

        catch (\InvalidArgumentException $e)
        {
            return response()->json([
            'message' => $e->getMessage()
            ], 422);
        }

        catch( \RuntimeException $e)
        {
            return response()->json(
                [
                    'message' => $e->getMessage()
                ], 409
            );
        }
    }
}
