<?php

namespace App\Http\Controllers;

use App\Models\Registration;
use App\Models\VisitorLog;
use Illuminate\Http\Request;

class CheckinController extends Controller
{
    public function index()
    {
        return view('checkin.index');
    }

    public function scan(Request $request)
    {
        $request->validate([
            'registration_id' => 'required|exists:registrations,id'
        ]);

        $registration = Registration::with(['user', 'event'])->find($request->registration_id);
        
        if (!$registration->event->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Event is not active'
            ]);
        }

        // Get last action
        $lastLog = $registration->visitorLogs()->latest()->first();
        $action = (!$lastLog || $lastLog->action === 'checkout') ? 'checkin' : 'checkout';

        // Create log
        VisitorLog::create([
            'registration_id' => $registration->id,
            'action' => $action,
            'created_by' => auth()->id(),
        ]);

        return response()->json([
            'success' => true,
            'action' => $action,
            'user' => $registration->user->full_name,
            'event' => $registration->event->name,
            'timestamp' => now()->format('Y-m-d H:i:s')
        ]);
    }
}