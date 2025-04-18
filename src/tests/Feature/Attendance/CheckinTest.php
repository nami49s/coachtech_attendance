<?php

namespace Tests\Feature\Attendance;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\Admin;

class CheckinTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 出勤ボタンが正しく機能する()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get('/attendance');
        $response->assertStatus(200);
        $response->assertSee('出勤');

        $this->post('/attendance/checkin');

        $response = $this->get('/attendance');
        $response->assertStatus(200);
        $response->assertSee('勤務中');
    }

    /** @test */
    public function 出勤は一日一回のみできる()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Attendance::create([
            'user_id' => $user->id,
            'date' => now()->toDateString(),
            'start_time' => now()->subHours(8),
            'end_time' => now()->subHour(),
        ]);

        $response = $this->get('/attendance');
        $response->assertStatus(200);
        $response->assertDontSee('<button>出勤</button>', false);
    }

    /** @test */
    public function 出勤時刻が管理画面で確認できる()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $this->post('/attendance/checkin');

        $admin = Admin::factory()->create();
        $this->actingAs($admin, 'admin');

        $response = $this->get('/admin/index');
        $response->assertStatus(200);
        $response->assertSee(now()->format('H:i'));
    }
}
