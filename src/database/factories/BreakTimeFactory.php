<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\BreakTime;
use App\Models\Attendance;
use Carbon\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\BreakTime>
 */
class BreakTimeFactory extends Factory
{
    protected $model = BreakTime::class;

    public function definition(): array
    {
        // ダミーのAttendanceを先に作成
        $attendance = Attendance::factory()->create();

        return $this->generateBreakTime($attendance);
    }

    // 外部のAttendanceに紐づける場合（シーダーやテストで便利）
    public function forAttendance(Attendance $attendance): static
    {
        return $this->state(fn () => $this->generateBreakTime($attendance));
    }

    // 実際のロジック部分
    private function generateBreakTime(Attendance $attendance): array
    {
        $checkin = Carbon::parse($attendance->date . ' ' . $attendance->checkin_time);
        $checkout = Carbon::parse($attendance->date . ' ' . $attendance->checkout_time);
        
        // 勤務時間（分）
        $workMinutes = $checkin->diffInMinutes($checkout);
        
        // 最低15分の休憩時間を確保
        if ($workMinutes < 30) {
            // 勤務時間が短すぎる場合、勤務時間の中央に短い休憩を設定
            $middlePoint = $checkin->copy()->addMinutes(floor($workMinutes / 2));
            $breakStart = $middlePoint->copy()->subMinutes(5);
            $breakEnd = $middlePoint->copy()->addMinutes(5);
        } else {
            // 休憩時間（最大でも勤務時間の30%に制限）
            $breakDuration = min(fake()->numberBetween(15, 60), floor($workMinutes * 0.3));
            
            // 休憩開始可能範囲: チェックイン後10分 〜 チェックアウト前(休憩時間+10分)
            $earliestBreakStart = $checkin->copy()->addMinutes(10);
            $latestBreakStart = $checkout->copy()->subMinutes($breakDuration + 10);
            
            // latestBreakStartがearliestBreakStartより前の場合の調整
            if ($latestBreakStart < $earliestBreakStart) {
                $breakStart = $checkin->copy()->addMinutes(5);
                $breakEnd = $breakStart->copy()->addMinutes(min(10, $workMinutes - 10));
            } else {
                // breakStart: earliestBreakStart 〜 latestBreakStart の間で生成
                $breakStart = Carbon::instance(
                    fake()->dateTimeBetween($earliestBreakStart, $latestBreakStart)
                );
                
                // breakEnd を勤務時間内に収める
                $breakEnd = $breakStart->copy()->addMinutes($breakDuration);
            }
        }
        
        return [
            'attendance_id' => $attendance->id,
            'break_start' => $breakStart->format('H:i'),
            'break_end' => $breakEnd->format('H:i'),
        ];
    }
}