<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\AttendanceRequest;
use App\Models\BreakRequest;

class AdminRequestTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;

    public function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create();
        $this->actingAs($this->admin, 'admin');
    }

    /** @test */
    public function 承認待ちの修正申請が一覧に表示される()
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create(['user_id' => $user->id]);
        AttendanceRequest::factory()->create([
            'attendance_id' => $attendance->id,
            'user_id' => $user->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.requests.index'));

        $response->assertStatus(200);
        $response->assertSee('承認待ち');
        $response->assertSee($attendance->user->name);
    }

    /** @test */
    public function 承認済みの修正申請が一覧に表示される()
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create(['user_id' => $user->id]);
        AttendanceRequest::factory()->create([
            'attendance_id' => $attendance->id,
            'user_id' => $user->id,
            'status' => 'approved',
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.requests.index'));

        $response->assertStatus(200);
        $response->assertSee('承認済み');
        $response->assertSee($attendance->user->name);
    }

    /** @test */
    public function 修正申請の詳細が正しく表示される()
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create(['user_id' => $user->id]);
        $request = AttendanceRequest::factory()->create([
            'attendance_id' => $attendance->id,
            'user_id' => $user->id,
            'checkin_time' => '09:00',
            'checkout_time' => '18:00',
            'remarks' => '交通遅延',
        ]);

        BreakRequest::factory()->create([
            'attendance_request_id' => $request->id,
            'user_id' => $user->id,
            'break_start' => '12:00',
            'break_end' => '13:00',
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.requests.show', $request->id));

        $response->assertStatus(200);
        $response->assertSee('09:00');
        $response->assertSee('18:00');
        $response->assertSee('12:00');
        $response->assertSee('13:00');
        $response->assertSee('交通遅延');
    }

    /** @test */
    public function 修正申請の承認処理が成功し勤怠が更新される()
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => now()->toDateString(),
            'checkin_time' => '10:00:00',
            'checkout_time' => '17:00:00',
        ]);

        $request = AttendanceRequest::factory()->create([
            'attendance_id' => $attendance->id,
            'checkin_time' => '09:00:00',
            'checkout_time' => '18:00:00',
            'status' => 'pending',
            'date' => now()->toDateString(),
        ]);

        $response = $this->actingAs($this->admin)->postJson(route('admin.requests.approve', $request->id));
        $response->assertOk();
        $response->assertJson(['success' => true]);

        $this->assertDatabaseHas('attendance_requests', [
            'id' => $request->id,
            'status' => 'approved',
        ]);

        $this->assertDatabaseHas('attendances', [
            'id' => $attendance->id,
            'checkin_time' => '09:00:00',
            'checkout_time' => '18:00:00',
        ]);
    }
}
