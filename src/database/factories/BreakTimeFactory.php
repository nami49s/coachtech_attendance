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
        $attendance = Attendance::factory()->create();

        return $this->generateBreakTime($attendance);
    }

    public function forAttendance(Attendance $attendance): static
    {
        return $this->state(fn () => $this->generateBreakTime($attendance));
    }

    private function generateBreakTime(Attendance $attendance): array
    {
        $checkin = Carbon::parse($attendance->date . ' ' . $attendance->checkin_time);
        $checkout = Carbon::parse($attendance->date . ' ' . $attendance->checkout_time);

        $workMinutes = $checkin->diffInMinutes($checkout);

        if ($workMinutes < 30) {
            $middlePoint = $checkin->copy()->addMinutes(floor($workMinutes / 2));
            $breakStart = $middlePoint->copy()->subMinutes(5);
            $breakEnd = $middlePoint->copy()->addMinutes(5);
        } else {
            $breakDuration = min(fake()->numberBetween(15, 60), floor($workMinutes * 0.3));

            $earliestBreakStart = $checkin->copy()->addMinutes(10);
            $latestBreakStart = $checkout->copy()->subMinutes($breakDuration + 10);

            if ($latestBreakStart < $earliestBreakStart) {
                $breakStart = $checkin->copy()->addMinutes(5);
                $breakEnd = $breakStart->copy()->addMinutes(min(10, $workMinutes - 10));
            } else {
                $breakStart = Carbon::instance(
                    fake()->dateTimeBetween($earliestBreakStart, $latestBreakStart)
                );

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