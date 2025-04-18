<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Admin;
use App\Models\Attendance;
use Carbon\Carbon;

class AdminAttendanceListTest extends TestCase
{
    use RefreshDatabase;

    private $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = Admin::factory()->create();
    }

    /** @test */
    public function 当日の全ユーザーの勤怠情報が正確に表示される()
    {
        $date = Carbon::today();

        $users = User::factory()->count(3)->create();
        foreach ($users as $user) {
            Attendance::factory()->create([
                'user_id' => $user->id,
                'date' => $date,
                'checkin_time' => '09:00:00',
                'checkout_time' => '18:00:00',
            ]);
        }

        $response = $this->actingAs($this->admin, 'admin')
            ->get(route('admin.index', ['date' => $date->format('Y-m-d')]));

        $response->assertStatus(200);
        foreach ($users as $user) {
            $response->assertSee($user->name);
            $response->assertSee('09:00');
            $response->assertSee('18:00');
        }
    }

    /** @test */
    public function 勤怠一覧に現在の日付が表示される()
    {
        $today = Carbon::today()->format('Y年m月d日');

        $response = $this->actingAs($this->admin, 'admin')
            ->get(route('admin.index'));

        $response->assertStatus(200);
        $response->assertSee($today);
    }

    /** @test */
    public function 「前日」ボタンで前日の勤怠情報が表示される()
    {
        $yesterday = Carbon::yesterday();

        $user = User::factory()->create();
        Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => $yesterday,
            'checkin_time' => '08:30:00',
            'checkout_time' => '17:30:00',
        ]);

        $response = $this->actingAs($this->admin, 'admin')
            ->get(route('admin.index', ['date' => $yesterday->format('Y-m-d')]));

        $response->assertStatus(200);
        $response->assertSee('08:30');
        $response->assertSee('17:30');
        $response->assertSee($yesterday->format('Y年m月d日'));
    }

    /** @test */
    public function 「翌日」ボタンで翌日の勤怠情報が表示される()
    {
        $tomorrow = Carbon::tomorrow();

        $user = User::factory()->create();
        Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => $tomorrow,
            'checkin_time' => '10:00:00',
            'checkout_time' => '19:00:00',
        ]);

        $response = $this->actingAs($this->admin, 'admin')
            ->get(route('admin.index', ['date' => $tomorrow->format('Y-m-d')]));

        $response->assertStatus(200);
        $response->assertSee('10:00');
        $response->assertSee('19:00');
        $response->assertSee($tomorrow->format('Y年m月d日'));
    }
}
