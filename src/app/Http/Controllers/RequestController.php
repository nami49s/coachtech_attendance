<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\AttendanceRequestForm;
use App\Models\AttendanceRequest;
use App\Models\BreakRequest;
use App\Models\Attendance;
use App\Models\BreakTime;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class RequestController extends Controller
{
    public function request($attendanceId)
    {
        $attendance = Attendance::findOrFail($attendanceId);

        return view('attendance.request', [
            'attendance' => $attendance
        ]);
    }

    public function storeAttendance(AttendanceRequestForm $request)
    {
        $attendanceRequest = AttendanceRequest::create([
            'user_id' => Auth::id(),
            'attendance_id' => $request->attendance_id,
            'checkin_time' => $request->checkin_time,
            'checkout_time' => $request->checkout_time,
            'remarks' => $request->remarks,
            'status' => 'pending',
            'date' => $request->date,
        ]);

        $breakIds = $request->break_id;
        $breakStarts = $request->break_start;
        $breakEnds = $request->break_end;

        foreach ($breakStarts as $i => $start) {
            $startTime = $start ?: null;
            $endTime = $breakEnds[$i] ?? null;
            $breakId = $breakIds[$i] ?? null;

            if (empty($startTime) && empty($endTime)) {
                continue;
            }

            $existingBreakRequest = BreakRequest::where('attendance_request_id', $attendanceRequest->id)
                                                ->where('break_id', $breakId)
                                                ->first();

            if ($existingBreakRequest) {
                $existingBreakRequest->update([
                    'break_start' => $startTime,
                    'break_end' => $endTime,
                    'status' => 'pending',
                ]);
            } else {
                BreakRequest::create([
                    'user_id' => Auth::id(),
                    'attendance_request_id' => $attendanceRequest->id,
                    'break_id' => $breakId,
                    'break_start' => $startTime,
                    'break_end' => $endTime,
                    'status' => 'pending',
                ]);
            }
        }

        return back()->with('success', '勤怠修正申請を送信しました');
    }

    public function index()
    {
        $pendingRequests = AttendanceRequest::with(['attendance', 'user'])
                                            ->where('status', 'pending')
                                            ->where('user_id', Auth::id())
                                            ->get();

        $approvedRequests = AttendanceRequest::with(['attendance', 'user'])
                                            ->where('status', 'approved')
                                            ->where('user_id', Auth::id())
                                            ->get();


        return view('requests.index', compact('pendingRequests', 'approvedRequests'));
    }

    public function show($id)
    {
        $attendanceRequest = AttendanceRequest::with('attendance')->findOrFail($id);

        $attendance = Attendance::where('user_id', $attendanceRequest->user_id)
                                ->where('date', $attendanceRequest->attendance->date)
                                ->first();

        return view('attendance.detail', compact('attendanceRequest', 'attendance'));
    }
}
