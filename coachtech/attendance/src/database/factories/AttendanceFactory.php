<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Attendance>
 */
class AttendanceFactory extends Factory
{
    protected $model = Attendance::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $date = Carbon::today()->subDays(rand(0, 30));

        $checkinTime = $date->copy()->addHours(fake()->numberBetween(8, 10)); // 8:00〜10:00の間
        $checkoutTime = (clone $checkinTime)->addHours(fake()->numberBetween(6, 10)); // 6〜10時間後に退勤

        return [
            'user_id' => User::factory(), // 関連するユーザーを作成
            'date' => $date->toDateString(),
            'checkin_time' => $checkinTime->toTimeString(),
            'checkout_time' => $checkoutTime->toTimeString(),
            'remarks' => fake()->optional()->sentence(),
        ];
    }
}
