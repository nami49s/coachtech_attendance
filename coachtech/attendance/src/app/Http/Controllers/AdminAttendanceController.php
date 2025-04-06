<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;
use App\Models\BreakTime;

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
}
