<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ReservationController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::middleware('auth:sanctum')->post(
    '/reservations',
    [ReservationController::class, 'store']
);

Route::middleware('auth:sanctum')->get('/reservations', [ReservationController::class, 'index']);
Route::middleware('auth:sanctum')->get('/reservations/{reservationId}', [ReservationController::class, 'show']);
Route::middleware('auth:sanctum')->patch('/reservations/{reservationId}/cancel', [ReservationController::class, 'cancel']);