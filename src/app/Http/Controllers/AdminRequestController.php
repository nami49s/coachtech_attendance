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

                $date = $attendanceRequest->date;

                $attendance = Attendance::updateOrCreate(
                    [
                        'user_id' => $attendanceRequest->user_id,
                        'date' => $date,
                    ],
                    [
                        'checkin_time' => $attendanceRequest->checkin_time,
                        'checkout_time' => $attendanceRequest->checkout_time,
                        'remarks' => $attendanceRequest->remarks,
                    ]
                );

                // この行が問題の原因 - 全ての既存の休憩を削除している
                // BreakTime::where('attendance_id', $attendance->id)->delete();

                // 代わりに、break IDとBreakTimeレコードのマッピングを維持する
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

                    // 既存の休憩を修正する場合
                    if ($breakRequest->break_id) {
                        // 既存の休憩を更新
                        BreakTime::updateOrCreate(
                            ['id' => $breakRequest->break_id],
                            [
                                'attendance_id' => $attendance->id,
                                'break_start' => $breakRequest->break_start,
                                'break_end' => $breakRequest->break_end,
                            ]
                        );

                        // このbreak IDを処理済みとして記録
                        $processedBreakIds[] = $breakRequest->break_id;
                    } else {
                        // 新しい休憩を作成
                        BreakTime::create([
                            'attendance_id' => $attendance->id,
                            'break_start' => $breakRequest->break_start,
                            'break_end' => $breakRequest->break_end,
                        ]);
                    }
                }

                // 削除された休憩（リクエストに含まれていないもの）を削除
                // ただし、上記で処理されなかったものだけ
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
