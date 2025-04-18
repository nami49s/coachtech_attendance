<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Admin;
use App\Models\Attendance;
use App\Models\BreakTime;
use Carbon\Carbon;

class AdminStaffTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = Admin::factory()->create([
            'email' => 'admin@example.com',
        ]);
        $this->user = User::factory()->create([
            'name' => 'テストユーザー',
            'email' => 'user@example.com',
        ]);
    }

    /** @test */
    public function 管理者は全一般ユーザーの名前とメールアドレスを確認できる()
    {
        $response = $this->actingAs($this->admin, 'admin')->get(route('admin.staffs'));

        $response->assertStatus(200);
        $response->assertSee($this->user->name);
        $response->assertSee($this->user->email);
    }

    /** @test */
    public function 管理者はユーザーの月次勤怠情報を確認できる()
    {
        $date = Carbon::today();

        Attendance::factory()->create([
            'user_id' => $this->user->id,
            'date' => $date,
            'checkin_time' => '09:00:00',
            'checkout_time' => '18:00:00',
        ]);

        $response = $this->actingAs($this->admin, 'admin')->get(route('admin.monthlyAttendance', ['user' => $this->user->id]));

        $response->assertStatus(200);
        $response->assertSee($date->format('m/d'));
        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }

    /** @test */
    public function 「前月」ボタンで前月の勤怠情報が表示される()
    {
        $lastMonth = Carbon::now()->subMonth()->startOfMonth();
        Attendance::factory()->create([
            'user_id' => $this->user->id,
            'date' => $lastMonth->copy()->addDays(3),
            'checkin_time' => '10:00:00',
            'checkout_time' => '19:00:00',
        ]);

        $response = $this->actingAs($this->admin, 'admin')
            ->get(route('admin.monthlyAttendance', [
                'user' => $this->user->id,
                'month' => $lastMonth->format('Y-m'),
            ]));

        $response->assertStatus(200);
        $response->assertSee($lastMonth->copy()->addDays(3)->format('m/d'));
    }

    /** @test */
    public function 「翌月」ボタンで翌月の勤怠情報が表示される()
    {
        $nextMonth = Carbon::now()->addMonth()->startOfMonth();
        Attendance::factory()->create([
            'user_id' => $this->user->id,
            'date' => $nextMonth->copy()->addDays(5),
            'checkin_time' => '08:00:00',
            'checkout_time' => '17:00:00',
        ]);

        $response = $this->actingAs($this->admin, 'admin')
            ->get(route('admin.monthlyAttendance', [
                'user' => $this->user->id,
                'month' => $nextMonth->format('Y-m'),
            ]));

        $response->assertStatus(200);
        $response->assertSee($nextMonth->copy()->addDays(5)->format('m/d'));
    }

    /** @test */
    public function 「詳細」リンクからその日の勤怠詳細画面に遷移できる()
    {
        $attendance = Attendance::factory()->create([
            'user_id' => $this->user->id,
            'date' => Carbon::today(),
            'checkin_time' => '09:30:00',
            'checkout_time' => '18:30:00',
        ]);

        $response = $this->actingAs($this->admin, 'admin')
            ->get(route('admin.show', ['attendance' => $attendance->id]));

        $response->assertStatus(200);
        $response->assertSee($this->user->name);
        $response->assertSee(Carbon::parse($attendance->date)->format('Y年'));
        $response->assertSee('09:30');
        $response->assertSee('18:30');
    }
}
