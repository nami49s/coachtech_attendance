<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Admin;
use App\Models\Attendance;
use App\Models\BreakTime;

class AdminAttendanceTest extends TestCase
{
    use RefreshDatabase;

    private $admin;
    private $user;
    private $attendance;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = Admin::factory()->create();
        $this->user = User::factory()->create();

        $this->attendance = Attendance::factory()->create([
            'user_id' => $this->user->id,
            'date' => now()->toDateString(),
            'checkin_time' => '09:00:00',
            'checkout_time' => '18:00:00',
            'remarks' => 'テスト勤務',
        ]);

        BreakTime::factory()->create([
            'attendance_id' => $this->attendance->id,
            'break_start' => '12:00:00',
            'break_end' => '13:00:00',
        ]);
    }

    private function actingAsAdmin()
    {
        return $this->actingAs($this->admin, 'admin');
    }

    /** @test */
    public function 勤怠詳細画面に正しい情報が表示される()
    {
        $response = $this->actingAsAdmin()->get(route('admin.show', ['attendance' => $this->attendance->id]));
        $response->assertStatus(200);
        $response->assertSee($this->user->name);
        $response->assertSee('09:00');
        $response->assertSee('18:00');
        $response->assertSee('テスト勤務');
    }

    /** @test */
    public function 出勤時間が退勤時間より後ならバリデーションエラー()
    {
        $response = $this->actingAsAdmin()->post(route('admin.attendance.update', $this->attendance->id), [
            'checkin_time' => '19:00',
            'checkout_time' => '18:00',
            'remarks' => 'テスト',
        ]);

        $response->assertSessionHasErrors(['checkout_time']);
    }

    /** @test */
    public function 休憩開始が退勤より後ならバリデーションエラー()
    {
        $response = $this->actingAsAdmin()->post(route('admin.attendance.update', $this->attendance->id), [
            'checkin_time' => '09:00',
            'checkout_time' => '18:00',
            'break_start' => ['19:00'],
            'break_end' => ['19:30'],
            'break_id' => [null],
            'remarks' => 'テスト',
        ]);

        $response->assertSessionHasErrors(['break_start.0']);
    }

    /** @test */
    public function 休憩終了が退勤より後ならバリデーションエラー()
    {
        $response = $this->actingAsAdmin()->post(route('admin.attendance.update', $this->attendance->id), [
            'checkin_time' => '09:00',
            'checkout_time' => '18:00',
            'break_start' => ['12:00'],
            'break_end' => ['19:30'],
            'break_id' => [null],
            'remarks' => 'テスト',
        ]);

        $response->assertSessionHasErrors(['break_end.0']);
    }

    /** @test */
    public function 備考欄が未入力ならバリデーションエラー()
    {
        $response = $this->actingAsAdmin()->post(route('admin.attendance.update', $this->attendance->id), [
            'checkin_time' => '09:00',
            'checkout_time' => '18:00',
            'break_start' => [],
            'break_end' => [],
            'break_id' => [],
            'remarks' => '',
        ]);

        $response->assertSessionHasErrors(['remarks']);
    }
}
