<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ReservationController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:50'],
            'date' => ['required', 'date', 'after_or_equal:today'],
            'time' => ['required', 'string', 'max:20'],
            'guests' => ['required', 'integer', 'min:1', 'max:20'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $reservation = Reservation::create([
            ...$validated,
            'reservation_number' => 'RES-'.strtoupper(Str::random(5)),
            'status' => 'pending',
        ]);

        return response()->json([
            'reservationNumber' => $reservation->reservation_number,
            'name' => $reservation->name,
            'email' => $reservation->email,
            'phone' => $reservation->phone,
            'date' => $reservation->date->toDateString(),
            'time' => $reservation->time,
            'guests' => $reservation->guests,
            'notes' => $reservation->notes,
            'status' => $reservation->status,
        ], 201);
    }

    public function show(string $reservationNumber)
    {
        $reservation = Reservation::where('reservation_number', $reservationNumber)->firstOrFail();

        return [
            'reservationNumber' => $reservation->reservation_number,
            'name' => $reservation->name,
            'date' => $reservation->date->toDateString(),
            'time' => $reservation->time,
            'guests' => $reservation->guests,
            'status' => $reservation->status,
        ];
    }
}
