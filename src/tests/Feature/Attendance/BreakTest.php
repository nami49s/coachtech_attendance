<?php

namespace Tests\Feature\Attendance;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use App\Models\User;
use App\Models\Attendance;
use Tests\TestCase;

class BreakTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 休憩ボタンが正しく機能する()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->post('/attendance/checkin');

        $response = $this->get('/attendance/working');
        $response->assertSee('休憩入');

        $this->post('/break/start');

        $response = $this->get('/attendance/break');
        $response->assertSee('休憩中');
    }

    /** @test */
    public function 休憩は一日に何回でもできる()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->post('/attendance/checkin');

        $this->post('/break/start');
        $this->post('/break/end');

        $response = $this->get('/attendance/working');
        $response->assertSee('休憩入');
    }

    /** @test */
    public function 休憩戻ボタンが正しく機能する()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->post('/attendance/checkin');
        $this->post('/break/start');

        $this->post('/break/end');

        $response = $this->get('/attendance/working');
        $response->assertSee('出勤中');
    }

    /** @test */
    public function 休憩戻は一日に何回でもできる()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->post('/attendance/checkin');

        $this->post('/break/start');
        $this->post('/break/end');

        $this->post('/break/start');

        $response = $this->get('/attendance/break');
        $response->assertSee('休憩戻');
    }

    /** @test */
    public function 休憩時刻が勤怠一覧画面で確認できる()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->post('/attendance/checkin');
        $this->post('/break/start');
        sleep(1);
        $this->post('/break/end');

        $response = $this->get('/attendance/index');
        $response->assertSee(now()->format('m/d'));
        $response->assertSee(':');
    }
}
