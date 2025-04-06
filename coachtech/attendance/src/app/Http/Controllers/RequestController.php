<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\AttendanceRequestForm;
use App\Models\AttendanceRequest;
use App\Models\BreakRequest;
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
        ]);

        $breakIds = $request->break_id;
        $breakStarts = $request->break_start;
        $breakEnds = $request->break_end;

        foreach ($breakStarts as $i => $start) {
            $startTime = $start ?: null;
            $endTime = $breakEnds[$i] ?? null;

            // 両方空なら無視
            if (empty($startTime) || empty($endTime)) {
                continue;
            }
            BreakRequest::create([
                'user_id' => Auth::id(),
                'attendance_requests_id' => $attendanceRequest->id,
                'break_id' => $breakIds[$i] ?? null,
                'break_start' => $startTime,
                'break_end' => $endTime,
                'status' => 'pending',
            ]);
        }

        return back()->with('success', '勤怠修正申請を送信しました');
    }
}
