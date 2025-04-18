<?php

namespace Tests\Feature\Attendance;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;
use App\Models\Admin;

class CheckoutTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 退勤ボタンが正しく機能する()
    {
        $user = User::factory()->create();

        $this->actingAs($user);
        $this->post('/attendance/checkin');

        $response = $this->get('/attendance/working');
        $response->assertSee('退勤');

        $this->post('/attendance/checkout');

        $response = $this->get('/attendance/checkout');
        $response->assertSee('退勤済');
    }

    /** @test */
    public function 退勤時刻が管理画面で確認できる()
    {
        $user = User::factory()->create();

        $this->actingAs($user);
        $this->post('/attendance/checkin');
        sleep(1);
        $this->post('/attendance/checkout');

        $admin = Admin::factory()->create();
        $this->actingAs($admin, 'admin');

        $response = $this->get('/attendance/index');

        $response->assertSee(':');
        $response->assertSee(now()->format('m/d'));
    }
}
