<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AttendanceSession;
use App\Models\AttendanceRecord;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AttendanceController extends Controller
{
    public function index()
    {
        $sessions = AttendanceSession::with('openedBy', 'closedBy')
            ->orderBy('date', 'desc')
            ->paginate(15);
            
        $activeSession = AttendanceSession::where('status', 'active')->first();
            
        return view('admin.attendance.index', compact('sessions', 'activeSession'));
    }

    public function store(Request $request)
    {
        $today = now()->format('Y-m-d');
        
        // Ensure no active session exists
        if (AttendanceSession::where('status', 'active')->exists()) {
            return back()->with('error', 'An active attendance session is already running.');
        }
        
        // Ensure no session was already created for today
        if (AttendanceSession::where('date', $today)->exists()) {
            return back()->with('error', 'An attendance session was already recorded for today.');
        }

        $session = AttendanceSession::create([
            'date' => $today,
            'status' => 'active',
            'opened_at' => now(),
            'opened_by' => $request->user()->id,
            'token' => Str::random(32), // Initial token
        ]);

        return redirect()->route('admin.attendance.show', $session)->with('success', 'Attendance session started.');
    }

    public function show(AttendanceSession $session)
    {
        $records = $session->records()->with('fellow')->orderBy('clock_in_time', 'desc')->get();
        return view('admin.attendance.show', compact('session', 'records'));
    }

    public function liveData(Request $request, AttendanceSession $session)
    {
        if ($session->status !== 'active') {
            return response()->json(['status' => 'closed']);
        }
        
        // Refresh token only when requested (e.g., every 15s) to make the QR dynamic but keep polling fast
        if ($request->boolean('refresh_token')) {
            $session->update(['token' => Str::random(32)]);
        }

        $records = $session->records()->with('fellow:id,name', 'fellow.curriculumProgress')->orderBy('clock_in_time', 'desc')->get();

        $tz = app(\App\Services\AdminSettingsService::class)->get('platform_timezone', 'Africa/Douala');

        return response()->json([
            'status' => 'active',
            'token' => $session->token,
            'records' => $records->map(function ($record) use ($tz) {
                return [
                    'id' => $record->id,
                    'fellow_name' => optional($record->fellow)->name ?? 'Deleted User',
                    'clock_in_time' => $record->clock_in_time->timezone($tz)->format('H:i:s'),
                    'clock_out_time' => $record->clock_out_time ? $record->clock_out_time->timezone($tz)->format('H:i:s') : null,
                    'status' => $record->status,
                    'activities_completed' => optional($record->fellow)->curriculumProgress ? $record->fellow->curriculumProgress->where('status', 'completed')->count() : 0,
                    'activities_started' => optional($record->fellow)->curriculumProgress ? $record->fellow->curriculumProgress->whereIn('status', ['started', 'submitted', 'under_review', 'peer_review'])->count() : 0,
                ];
            })
        ]);
    }

    public function close(Request $request, AttendanceSession $session)
    {
        if ($session->status === 'closed') {
            return back()->with('error', 'Session is already closed.');
        }

        // 1. Mark clocked-in but not clocked-out as absent
        $session->records()->whereNull('clock_out_time')->where('status', 'present')->update([
            'status' => 'absent',
            'admin_notes' => 'Auto-marked absent: Did not clock out before session close.',
        ]);

        // 2. Find all fellows who didn't clock in at all, and create absent records for them
        $allFellows = User::fellows()->pluck('id');
        $clockedInFellows = $session->records()->pluck('fellow_id');
        $missingFellows = $allFellows->diff($clockedInFellows);

        $now = now();
        $absentRecords = [];
        foreach ($missingFellows as $fellowId) {
            $absentRecords[] = [
                'id' => Str::uuid()->toString(),
                'session_id' => $session->id,
                'fellow_id' => $fellowId,
                'status' => 'absent',
                'admin_notes' => 'Auto-marked absent: Did not clock in.',
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }
        
        if (!empty($absentRecords)) {
            AttendanceRecord::insert($absentRecords);
        }

        $session->update([
            'status' => 'closed',
            'closed_at' => now(),
            'closed_by' => $request->user()->id,
        ]);

        return redirect()->route('admin.attendance.index')->with('success', 'Attendance session closed and absences recorded.');
    }

    public function updateRecord(Request $request, AttendanceSession $session, AttendanceRecord $record)
    {
        $request->validate([
            'status' => 'required|in:present,absent,late,on_leave',
            'admin_notes' => 'nullable|string',
        ]);

        $record->update([
            'status' => $request->status,
            'admin_notes' => $request->admin_notes,
            'is_manually_adjusted' => true,
        ]);

        return back()->with('success', 'Attendance record updated.');
    }
}
