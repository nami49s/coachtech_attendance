<?php

namespace Tests\Feature\Attendance;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;
use App\Models\AttendanceRequest;
use App\Models\BreakRequest;
use Carbon\Carbon;

class AttendanceRequestTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $attendance;
    protected $breakTime;

    public function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();

        $this->attendance = Attendance::create([
            'user_id' => $this->user->id,
            'date' => Carbon::today()->format('Y-m-d'),
            'checkin_time' => '09:00',
            'checkout_time' => '18:00',
            'remarks' => 'テスト用備考'
        ]);

        $this->breakTime = BreakTime::create([
            'attendance_id' => $this->attendance->id,
            'break_start' => '12:00',
            'break_end' => '13:00'
        ]);
    }

    /** @test */
    public function 退勤時間が出勤時間より前になっている場合_エラーメッセージが表示される()
    {
        $response = $this->actingAs($this->user)
            ->post(route('attendance.request.submit'), [
                'attendance_id' => $this->attendance->id,
                'date' => $this->attendance->date,
                'checkin_time' => '10:00',
                'checkout_time' => '09:00',
                'break_end' => ['11:30'],
                'break_id' => [''],
                'remarks' => '修正申請です',
            ]);

        $response->assertSessionHasErrors([
            'checkout_time' => '出勤時間もしくは退勤時間が不適切な値です。',
        ]);
    }

    /** @test */
    public function 休憩開始時間が退勤時間より後になっている場合_エラーメッセージが表示される()
    {
        $response = $this->actingAs($this->user)
            ->post(route('attendance.request.submit'), [
                'attendance_id' => $this->attendance->id,
                'date' => $this->attendance->date,
                'checkin_time' => '09:00',
                'checkout_time' => '18:00',
                'remarks' => '備考テスト',
                'break_id' => [$this->breakTime->id],
                'break_start' => ['19:00'],
                'break_end' => ['20:00']
            ]);

        $response->assertSessionHasErrors([
            'break_start.0' => '休憩時間が勤務時間外です。',
        ]);
    }

    /** @test */
    public function 休憩終了時間が退勤時間より後になっている場合_エラーメッセージが表示される()
    {
        $response = $this->actingAs($this->user)
            ->post(route('attendance.request.submit'), [
                'attendance_id' => $this->attendance->id,
                'date' => $this->attendance->date,
                'checkin_time' => '09:00',
                'checkout_time' => '18:00',
                'remarks' => '備考テスト',
                'break_id' => [$this->breakTime->id],
                'break_start' => ['12:00'],
                'break_end' => ['19:00']
            ]);

        $response->assertSessionHasErrors([
            'break_end.0' => '休憩時間が勤務時間外です。',
        ]);
    }

    /** @test */
    public function 備考欄が未入力の場合のエラーメッセージが表示される()
    {
        $response = $this->actingAs($this->user)
            ->post(route('attendance.request.submit'), [
                'attendance_id' => $this->attendance->id,
                'date' => $this->attendance->date,
                'checkin_time' => '09:00',
                'checkout_time' => '18:00',
                'remarks' => '',
                'break_id' => [$this->breakTime->id],
                'break_start' => ['12:00'],
                'break_end' => ['13:00']
            ]);

        $response->assertSessionHasErrors([
            'remarks' => '備考を記入してください。',
        ]);
    }

    /** @test */
    public function 修正申請処理が実行される()
    {
        $response = $this->actingAs($this->user)
            ->post(route('attendance.request.submit'), [
                'attendance_id' => $this->attendance->id,
                'date' => $this->attendance->date,
                'checkin_time' => '10:00',
                'checkout_time' => '19:00',
                'remarks' => '修正申請テスト',
                'break_id' => [$this->breakTime->id],
                'break_start' => ['12:30'],
                'break_end' => ['13:30']
            ]);

        $response->assertSessionHas('success', '勤怠修正申請を送信しました');

        $this->assertDatabaseHas('attendance_requests', [
            'user_id' => $this->user->id,
            'attendance_id' => $this->attendance->id,
            'checkin_time' => '10:00',
            'checkout_time' => '19:00',
            'remarks' => '修正申請テスト',
            'status' => 'pending'
        ]);

        $this->assertDatabaseHas('break_requests', [
            'break_start' => '12:30',
            'break_end' => '13:30',
            'status' => 'pending'
        ]);
    }

    /** @test */
    public function 「承認待ち」にログインユーザーが行った申請が全て表示されていること()
    {
        $this->actingAs($this->user)
            ->post(route('attendance.request.submit'), [
                'attendance_id' => $this->attendance->id,
                'date' => $this->attendance->date,
                'checkin_time' => '10:00',
                'checkout_time' => '19:00',
                'remarks' => '申請テスト1',
                'break_id' => [$this->breakTime->id],
                'break_start' => ['12:00'],
                'break_end' => ['13:00']
            ]);

        $this->actingAs($this->user)
            ->post(route('attendance.request.submit'), [
                'attendance_id' => $this->attendance->id,
                'date' => $this->attendance->date,
                'checkin_time' => '08:00',
                'checkout_time' => '17:00',
                'remarks' => '申請テスト2',
                'break_id' => [$this->breakTime->id],
                'break_start' => ['12:00'],
                'break_end' => ['13:00']
            ]);

        $response = $this->actingAs($this->user)
            ->get(route('requests.index'));

        $response->assertStatus(200);
        $response->assertSee('申請テスト1');
        $response->assertSee('申請テスト2');
    }

    /** @test */
    public function 「承認済み」に管理者が承認した修正申請が全て表示されている()
    {
        $admin = User::factory()->create();

        $this->actingAs($this->user)
            ->post(route('attendance.request.submit'), [
                'attendance_id' => $this->attendance->id,
                'date' => $this->attendance->date,
                'checkin_time' => '10:00',
                'checkout_time' => '19:00',
                'remarks' => '承認テスト',
                'break_id' => [$this->breakTime->id],
                'break_start' => ['12:00'],
                'break_end' => ['13:00']
            ]);

        $request = \App\Models\AttendanceRequest::latest()->first();
        $request->status = 'approved';
        $request->save();

        $response = $this->actingAs($this->user)
            ->get(route('requests.index'));

        $response->assertStatus(200);
        $response->assertSee('承認テスト');
        $response->assertSee('approved');
    }

    /** @test */
    public function 各申請の「詳細」を押下すると申請詳細画面に遷移する()
    {
        $this->actingAs($this->user)
            ->post(route('attendance.request.submit'), [
                'attendance_id' => $this->attendance->id,
                'date' => $this->attendance->date,
                'checkin_time' => '10:00',
                'checkout_time' => '19:00',
                'remarks' => '詳細テスト',
                'break_id' => [$this->breakTime->id],
                'break_start' => ['12:00'],
                'break_end' => ['13:00']
            ]);

        $request = \App\Models\AttendanceRequest::latest()->first();

        $response = $this->actingAs($this->user)
            ->get(route('requests.show', ['id' => $request->id]));

        $response->assertStatus(200);
        $response->assertViewIs('attendance.detail');
        $response->assertSee('詳細テスト');
    }
}
