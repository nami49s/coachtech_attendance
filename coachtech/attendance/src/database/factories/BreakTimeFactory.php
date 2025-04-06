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
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $attendance = Attendance::inRandomOrder()->first();
        $breakStart = Carbon::parse($attendance->checkin_time)->addHours(fake()->numberBetween(1, 3));
        $breakEnd = (clone $breakStart)->addMinutes(fake()->numberBetween(30, 60));

        return [
            'attendance_id' => $attendance->id,
            'break_start' => $breakStart->toTimeString(),
            'break_end' => $breakEnd->toTimeString(),
        ];
    }
}
