<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Attendance;
use App\Models\User;

class AttendanceRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'date',
        'attendance_id',
        'checkin_time',
        'checkout_time',
        'remarks',
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

    public function breakRequests()
    {
        return $this->hasMany(BreakRequest::class, 'attendance_request_id');
    }

    public function breaks()
    {
        return $this->hasMany(BreakRequest::class);
    }
}