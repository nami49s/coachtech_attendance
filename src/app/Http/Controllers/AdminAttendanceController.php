<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;
use App\Models\BreakTime;
use App\Http\Requests\UpdateAttendanceRequest;

class AdminAttendanceController extends Controller
{
    public function dashboard(Request $request)
    {
        $date = $request->query('date') ? Carbon::parse($request->query('date')) : Carbon::today();
        $attendances = Attendance::whereDate('date', $date)
            ->with('user', 'breaks')
            ->get()
            ->map(function ($attendance) {
                $attendance->break_time = $attendance->breaks->sum('duration');
                $checkinTime = Carbon::parse($attendance->checkin_time);
                $checkoutTime = $attendance->checkout_time ? Carbon::parse($attendance->checkout_time) : null;
                $attendance->work_time = $checkoutTime
                    ? $checkoutTime->diffInSeconds($checkinTime) - $attendance->break_time
                    : null;
                return $attendance;
            });

        return view('admin.index', [
            'date' => $date,
            'attendances' => $attendances
        ]);
    }

    public function staffs()
    {
        $users = User::all();

        $usersWithAttendance = $users->map(function ($user) {
            $attendances = Attendance::where('user_id', $user->id)
                ->whereMonth('date', Carbon::now()->month)
                ->get();

            $user->attendances = $attendances;

            return $user;
        });

        return view('admin.staffs', compact('usersWithAttendance'));
    }

    public function show(Attendance $attendance)
    {
        return view('admin.show', compact('attendance'));
    }

    public function monthlyAttendance(User $user, Request $request)
    {
        $month = $request->query('month', now()->format('Y-m'));
        $attendances = Attendance::where('user_id', $user->id)
            ->whereYear('date', substr($month, 0, 4))
            ->whereMonth('date', substr($month, 5, 2))
            ->get();

        return view('admin.monthlyattendance', [
            'attendances' => $attendances,
            'month' => $month,
            'user' => $user
        ]);
    }

    public function update(UpdateAttendanceRequest $request, $id)
    {
        $attendance = Attendance::findOrFail($id);

        // 出勤・退勤・備考の更新
        $attendance->checkin_time = $request->input('checkin_time');
        $attendance->checkout_time = $request->input('checkout_time');
        $attendance->remarks = $request->input('remarks');
        $attendance->save();

        // 既存の休憩を削除
        $attendance->breaks()->delete();

        // 新しい休憩データを保存
        $breakStarts = $request->input('break_start');
        $breakEnds = $request->input('break_end');

        if ($breakStarts && $breakEnds) {
            foreach ($breakStarts as $index => $start) {
                if ($start || $breakEnds[$index]) {
                    BreakTime::create([
                        'attendance_id' => $attendance->id,
                        'break_start' => $start,
                        'break_end' => $breakEnds[$index],
                    ]);
                }
            }
        }

        return redirect()->route('admin.show', ['attendance' => $attendance->id])->with('success', '勤怠情報を更新しました。');
    }
}
