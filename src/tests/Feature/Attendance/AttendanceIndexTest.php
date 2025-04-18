<?php

namespace Tests\Feature\Attendance;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;

class AttendanceIndexTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 自分が行った勤怠情報が全て表示されている()
    {
        $user = User::factory()->create();

        foreach ([2, 1, 0] as $i) {
            Attendance::factory()->forUser($user)->create([
                'date' => now()->subDays($i)->toDateString(),
            ]);
        }

        $this->actingAs($user);

        $response = $this->get('/attendance/index');
        $attendances = Attendance::where('user_id', $user->id)->get();

        foreach ($attendances as $attendance) {
            $response->assertSee(Carbon::parse($attendance->date)->format('m/d'));
        }
    }

    /** @test */
    public function 勤怠一覧画面に遷移した際に現在の月が表示される()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get('/attendance/index');

        $response->assertSee(now()->format('Y年m月'));
    }

    /** @test */
    public function 前月を押下すると前月の情報が表示される()
    {
        $user = User::factory()->create();

        $lastMonth = now()->subMonth()->startOfMonth();
        Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => $lastMonth->copy()->addDays(3),
        ]);

        $this->actingAs($user);

        $response = $this->get('/attendance/index?month=' . $lastMonth->format('Y-m'));

        $response->assertSee($lastMonth->format('Y年m月'));
        $response->assertSee($lastMonth->copy()->addDays(3)->format('m/d'));
    }

    /** @test */
    public function 翌月を押下すると翌月の情報が表示される()
    {
        $user = User::factory()->create();

        $nextMonth = now()->addMonth()->startOfMonth();
        Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => $nextMonth->copy()->addDays(5),
        ]);

        $this->actingAs($user);

        $response = $this->get('/attendance/index?month=' . $nextMonth->format('Y-m'));

        $response->assertSee($nextMonth->format('Y年m月'));
        $response->assertSee($nextMonth->copy()->addDays(5)->format('m/d'));
    }

    /** @test */
    public function 詳細ボタンを押すと勤怠詳細画面に遷移する()
    {
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
        ]);

        $this->actingAs($user);

        $response = $this->get('/attendance/detail/' . $attendance->id);

        $response->assertStatus(200);
        $response->assertSee(Carbon::parse($attendance->date)->format('Y-m-d'));
    }
}
