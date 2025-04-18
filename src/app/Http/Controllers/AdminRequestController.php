<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\AttendanceRequest;
use App\Models\Attendance;
use App\Models\BreakTime;
use App\Models\BreakRequest;
use Illuminate\Support\Facades\Auth;


class AdminRequestController extends Controller
{
    public function index()
    {
        $pendingRequests = AttendanceRequest::where('status', 'pending')->with('user')->get();
        $approvedRequests = AttendanceRequest::where('status', 'approved')->with('user')->get();

        return view('admin.requests.index', compact('pendingRequests', 'approvedRequests'));
    }

    public function show($id)
    {
        $attendanceRequest = AttendanceRequest::with('user', 'attendance', 'breakRequests')->findOrFail($id);
        return view('admin.requests.show', compact('attendanceRequest'));
    }

    public function approve($id)
    {
        try {
            DB::transaction(function () use ($id) {
                $attendanceRequest = AttendanceRequest::findOrFail($id);
                $attendanceRequest->status = 'approved';
                $attendanceRequest->save();

                $attendance = Attendance::findOrFail($attendanceRequest->attendance_id);
                $attendance->checkin_time = $attendanceRequest->checkin_time;
                $attendance->checkout_time = $attendanceRequest->checkout_time;
                $attendance->remarks = $attendanceRequest->remarks;
                $attendance->save();

                $breakTimeMap = BreakTime::where('attendance_id', $attendance->id)
                    ->pluck('id', 'id')
                    ->toArray();

                $pendingBreakRequests = BreakRequest::where('attendance_request_id', $attendanceRequest->id)
                    ->where('status', 'pending')
                    ->orderBy('id')
                    ->get();

                $processedBreakIds = [];

                foreach ($pendingBreakRequests as $breakRequest) {
                    if (empty($breakRequest->break_start) && empty($breakRequest->break_end)) {
                        continue;
                    }

                    $breakRequest->status = 'approved';
                    $breakRequest->save();

                    if ($breakRequest->break_id) {
                        BreakTime::updateOrCreate(
                            ['id' => $breakRequest->break_id],
                            [
                                'attendance_id' => $attendance->id,
                                'break_start' => $breakRequest->break_start,
                                'break_end' => $breakRequest->break_end,
                            ]
                        );
                        $processedBreakIds[] = $breakRequest->break_id;
                    } else {
                        $newBreak = BreakTime::create([
                            'attendance_id' => $attendance->id,
                            'break_start' => $breakRequest->break_start,
                            'break_end' => $breakRequest->break_end,
                        ]);
                        $processedBreakIds[] = $newBreak->id;
                    }
                }
                foreach ($breakTimeMap as $breakTimeId) {
                    if (!in_array($breakTimeId, $processedBreakIds)) {
                        BreakTime::where('id', $breakTimeId)->delete();
                    }
                }
            });

            return response()->json(['success' => true]);

        } catch (\Throwable $e) {
            \Log::error('承認エラー：' . $e->getMessage());
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }
}
