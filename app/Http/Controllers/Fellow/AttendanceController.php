<?php

namespace App\Http\Controllers\Fellow;

use App\Http\Controllers\Controller;
use App\Models\AttendanceSession;
use App\Models\AttendanceRecord;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $records = AttendanceRecord::with('session')
            ->where('fellow_id', $request->user()->id)
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('fellow.attendance.index', compact('records'));
    }

    public function clockIn(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
        ]);

        $session = AttendanceSession::where('status', 'active')
            ->where('token', $request->token)
            ->first();

        if (!$session) {
            return back()->with('error', 'Invalid or expired QR code. Please scan the current code on the screen.');
        }

        // Check if already clocked in
        $existingRecord = AttendanceRecord::where('session_id', $session->id)
            ->where('fellow_id', $request->user()->id)
            ->first();

        if ($existingRecord) {
            return back()->with('error', 'You have already clocked in for this session.');
        }

        AttendanceRecord::create([
            'session_id' => $session->id,
            'fellow_id' => $request->user()->id,
            'clock_in_time' => now(),
            'status' => 'present',
        ]);

        return back()->with('success', 'Successfully clocked in!');
    }

    public function clockOut(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
        ]);

        $session = AttendanceSession::where('status', 'active')
            ->where('token', $request->token)
            ->first();

        if (!$session) {
            return back()->with('error', 'Invalid or expired QR code. Please scan the current code on the screen.');
        }

        $record = AttendanceRecord::where('session_id', $session->id)
            ->where('fellow_id', $request->user()->id)
            ->first();

        if (!$record) {
            return back()->with('error', 'You have not clocked in for this session.');
        }

        if ($record->clock_out_time) {
            return back()->with('error', 'You have already clocked out.');
        }

        $record->update([
            'clock_out_time' => now(),
        ]);

        return back()->with('success', 'Successfully clocked out!');
    }
}
