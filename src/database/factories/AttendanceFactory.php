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

    public function definition(): array
    {
        // 0〜30日前のランダムな日付
        $date = Carbon::today()->subDays(fake()->numberBetween(0, 30));

        // 出勤時間：08:00〜10:00の間
        $checkinTime = (clone $date)->addHours(fake()->numberBetween(8, 10));

        // 退勤時間：出勤から6〜10時間後
        $checkoutTime = (clone $checkinTime)->addHours(fake()->numberBetween(6, 10));

        return [
            'user_id' => User::factory(), // 関連するユーザーを自動生成
            'date' => $date->toDateString(),
            'checkin_time' => $checkinTime->format('H:i'),
            'checkout_time' => $checkoutTime->format('H:i'),
            'remarks' => fake()->optional()->sentence(),
        ];
    }

    /**
     * 任意のユーザーに紐づけたい場合用
     */
    public function forUser(User $user): static
    {
        return $this->state(fn () => [
            'user_id' => $user->id,
        ]);
    }
}
