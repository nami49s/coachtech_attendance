<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\UpdateAttendanceRequest;
use App\Models\Attendance;
use App\Models\BreakTime;
use Carbon\Carbon;
use App\Models\AttendanceRequest;
use App\Models\BreakRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AttendanceController extends Controller
{
    private function getTodayAttendance()
    {
        $user = Auth::user();
        $today = Carbon::today();

        return Attendance::where('user_id', $user->id)
            ->where('date', $today)
            ->first();
    }

    public function show()
    {
        $attendance = $this->getTodayAttendance();

        if (!$attendance) {
            return view('attendance.checkin');
        }

        if ($attendance->checkout_time) {
            return view('attendance.checkout');
        }

        $activeBreak = BreakTime::where('attendance_id', $attendance->id)
            ->whereNull('break_end')
            ->first();

        if ($activeBreak) {
            return view('attendance.break');
        }

        return view('attendance.working');
    }

    public function checkin()
    {
        $attendance = $this->getTodayAttendance();

        if ($attendance) {
            return redirect()->route('attendance.working')
                ->with('error', 'すでに出勤しています。');
        }

        DB::transaction(function () {
            $attendance = Attendance::create([
                'user_id' => Auth::id(),
                'date' => Carbon::today(),
                'checkin_time' => Carbon::now(),
            ]);
        });

        return redirect()->route('attendance.working')
            ->with('success', '出勤しました。')
            ->with('attendance', $attendance);
    }

    public function working()
    {
        return view('attendance.working');
    }

    public function checkout()
    {
        $attendance = $this->getTodayAttendance();

        if (!$attendance) {
            return redirect()->route('attendance.checkin')
                ->with('error', '出勤していません。');
        }

        if ($attendance->checkout_time) {
            return back()->with('error', 'すでに退勤済みです。');
        }

        DB::transaction(function () use ($attendance) {
            $attendance->update([
                'checkout_time' => Carbon::now(),
            ]);
        });

        return redirect()->route('attendance.checkout.view')
            ->with('success', '退勤しました。');
    }

    public function checkoutView()
    {
        return view('attendance.checkout');
    }

    public function breakStart()
    {
        $attendance = $this->getTodayAttendance();

        if (!$attendance) {
            return redirect()->route('attendance.checkin')
                ->with('error', '出勤していません。');
        }

        DB::transaction(function () use ($attendance) {
            BreakTime::create([
                'attendance_id' => $attendance->id,
                'break_start' => Carbon::now(),
                'break_end' => null,
            ]);
        });

        return redirect()->route('attendance.break')
            ->with('success', '休憩を開始しました。');
    }

    public function breakView()
    {
        return view('attendance.break');
    }

    public function breakEnd()
    {
        $attendance = $this->getTodayAttendance();

        if (!$attendance) {
            return redirect()->route('attendance.checkin')
                ->with('error', '出勤していません。');
        }

        $break = BreakTime::where('attendance_id', $attendance->id)
            ->whereNull('break_end')
            ->latest()
            ->first();

        if (!$break) {
            return redirect()->route('attendance.break')
                ->with('error', '休憩を開始していません。');
        }

        DB::transaction(function () use ($break) {
            $break->update([
                'break_end' => Carbon::now(),
            ]);
        });

        return redirect()->route('attendance.working')
            ->with('success', '休憩を終了しました。');
    }

    public function request()
    {
        return view('attendance.request');
    }

    public function index(Request $request)
    {
        $user = Auth::user();

        $currentMonth = $request->query('month', Carbon::now()->format('Y-m'));
        $currentDate = Carbon::createFromFormat('Y-m', $currentMonth);
        $prevMonth = $currentDate->copy()->subMonth()->format('Y-m');
        $nextMonth = $currentDate->copy()->addMonth()->format('Y-m');

        $attendances = Attendance::where('user_id', $user->id)
            ->whereYear('date', $currentDate->year)
            ->whereMonth('date', $currentDate->month)
            ->with('breaks')
            ->orderBy('date', 'asc')
            ->get();

        return view('attendance.index', compact('attendances', 'currentMonth', 'prevMonth', 'nextMonth'));
    }

    public function detail($id)
    {
        $attendance = Attendance::with('user', 'breaks')->findOrFail($id);

        $attendanceRequest = AttendanceRequest::where('attendance_id', $attendance->id)
                        ->where('status', 'pending')
                        ->latest()
                        ->first();

        $breakRequests = $attendanceRequest ? $attendanceRequest->breakRequests : [];

        return view('attendance.detail', compact('attendance', 'attendanceRequest', 'breakRequests'));
    }
}
