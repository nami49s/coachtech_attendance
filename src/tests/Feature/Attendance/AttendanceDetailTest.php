<?php

namespace Tests\Feature\Attendance;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;

class AttendanceDetailTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $attendance;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'name' => 'テストユーザー',
        ]);

        $this->attendance = Attendance::factory()->for($this->user)->create([
            'date' => '2024-04-15',
            'checkin_time' => '09:00',
            'checkout_time' => '18:00',
        ]);

        $this->attendance->breaks()->create([
            'break_start' => '12:00',
            'break_end' => '13:00',
        ]);

        $this->actingAs($this->user);
    }

    /** @test */
    public function 勤怠詳細画面の名前がログインユーザーの氏名になっている()
    {
        $response = $this->get("/attendance/detail/{$this->attendance->id}");
        $response->assertSee($this->user->name);
    }

    /** @test */
    public function 勤怠詳細画面の日付が勤怠データの日付になっている()
    {
        $response = $this->get("/attendance/detail/{$this->attendance->id}");
        $response->assertSee('2024-04-15');
    }

    /** @test */
    public function 出勤退勤時間が正しく表示されている()
    {
        $response = $this->get("/attendance/detail/{$this->attendance->id}");
        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }

    /** @test */
    public function 休憩時間が正しく表示されている()
    {
        $response = $this->get("/attendance/detail/{$this->attendance->id}");
        $response->assertSee('12:00');
        $response->assertSee('13:00');
    }
}
