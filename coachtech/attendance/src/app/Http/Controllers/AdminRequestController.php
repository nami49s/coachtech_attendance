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
        DB::transaction(function () use ($id) {
            // 1. attendance_requests から取得＆承認
            $attendanceRequest = AttendanceRequest::findOrFail($id);
            $attendanceRequest->status = 'approved';
            $attendanceRequest->save();

            // 2. attendances テーブルを更新 or 作成
            $date = optional($attendanceRequest->attendance)->date;
            $attendance = Attendance::updateOrCreate(
                [
                    'user_id' => $attendanceRequest->user_id,
                    'date' => $date,
                ],
                [
                    'checkin_time' => $attendanceRequest->checkin_time,  // 申請時の出勤時間を使用
                    'checkout_time' => $attendanceRequest->checkout_time,  // 申請時の退勤時間を使用
                    'remarks' => $attendanceRequest->remarks,  // 申請時の備考を使用
                ]
            );

            // 3. break_attendances も処理
            foreach ($attendanceRequest->breakRequests as $breakRequest) {
                $breakRequest->status = 'approved';
                $breakRequest->save();

                // 該当する breaks レコードを更新 or 作成
                BreakTime::updateOrCreate(
                    [
                        'attendance_id' => $attendance->id,
                    ],
                    [
                        'break_start' => $breakRequest->break_start,
                        'break_end' => $breakRequest->break_end,
                    ]
                );
            }
        });

        return response()->json(['success' => true]);  // 承認成功のレスポンスを返す
    }
}
