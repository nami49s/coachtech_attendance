<?php

namespace Tests\Feature\Attendance;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;
use Carbon\Carbon;

class AttendanceStatusTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 勤務外の場合_ステータスが勤務外と表示される()
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $response = $this->get('/attendance');
        $response->assertSee('勤務外');
    }

    /** @test */
    public function 出勤中の場合_ステータスが出勤中と表示される()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Attendance::create([
            'user_id' => $user->id,
            'date' => now()->toDateString(),
            'start_time' => now()->subHours(2),
            'end_time' => null,
        ]);

        $response = $this->get('/attendance');
        $response->assertSee('出勤中');
    }

    /** @test */
    public function 休憩中の場合_ステータスが休憩中と表示される()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => now()->toDateString(),
            'start_time' => now()->subHours(3),
            'end_time' => null,
        ]);

        BreakTime::create([
            'attendance_id' => $attendance->id,
            'break_start' => now()->subMinutes(30),
            'break_end' => null,
        ]);

        $response = $this->get('/attendance');
        $response->assertSee('休憩中');
    }

    /** @test */
    public function 退勤済の場合_ステータスが退勤済と表示される()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $today = now()->toDateString();
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => $today,
            'checkin_time' => now()->setDateFrom($today)->subHours(9),
            'checkout_time' => now()->setDateFrom($today)->subHour(),
        ]);
        BreakTime::create([
            'attendance_id' => $attendance->id,
            'break_start' => now()->subHours(6),
            'break_end' => now()->subHours(5),
        ]);

        $response = $this->followingRedirects()->get('/attendance');
        $response->assertStatus(200);
        $response->assertSee('退勤済');
    }
}
