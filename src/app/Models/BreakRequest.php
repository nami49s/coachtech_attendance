<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Attendance;
use App\Models\User;
use App\Models\BreakTime;

class BreakRequest extends Model
{
    protected $fillable = [
        'user_id',
        'attendance_request_id',
        'attendance_id',
        'break_id',
        'break_start',
        'break_end',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }

    public function breaks()
    {
        return $this->belongsTo(BreakTime::class);
    }

    public function attendanceRequest()
    {
        return $this->belongsTo(AttendanceRequest::class, 'attendance_request_id');
    }
}