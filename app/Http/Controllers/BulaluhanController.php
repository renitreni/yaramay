<?php

namespace App\Http\Controllers;

use App\Mail\BulaluhanContactMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\BulaluhanReservationMail;
use Illuminate\Support\Facades\Validator;

class BulaluhanController extends Controller
{
    public function book(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255',
            'phone_number' => 'required|string|max:20',
            'date' => 'required|date',
            'time' => 'required|date_format:H:i',
            'number_of_guests' => 'required|integer|min:1',
            'message' => 'required|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Send email
        Mail::to(['ronald@bulalohansariyadh.xyz'])->send(new BulaluhanReservationMail($request->all()));

        return response()->json([
            'message' => 'Reservation created successfully'
        ], 201);
    }

    public function contact(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255',
            'subject' => 'required|string|email|max:255',
            'message' => 'required|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Send email
        Mail::to(['ronald@bulalohansariyadh.xyz'])->send(new BulaluhanContactMail($request->all()));

        return response()->json([
            'message' => 'Contact has been sent successfully'
        ], 201);
    }
}
