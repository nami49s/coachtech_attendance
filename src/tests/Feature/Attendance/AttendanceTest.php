<?php

namespace Tests\Feature\Attendance;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Carbon\Carbon;
use Tests\TestCase;
use App\Models\User;

class AttendanceTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 未ログインユーザーは勤怠打刻画面にアクセスできない()
    {
        $response = $this->get('/attendance');

        $response->assertRedirect('/login');
    }

    /** @test */
    public function ログイン済みユーザーは現在の日時を勤怠打刻画面に表示できる()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Carbon::setLocale('ja');

        $fixedNow = Carbon::create(2025, 4, 16, 14, 30);
        Carbon::setTestNow($fixedNow);

        $now = now();

        $expectedDate = $now->isoFormat('YYYY年M月D日(ddd)');
        $expectedTime = $now->format('H:i');

        $response = $this->get('/attendance');

        $response->assertSee($expectedDate, false);
        $response->assertSee($expectedTime, false);
    }
}
