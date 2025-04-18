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
        $date = Carbon::today()->subDays(fake()->numberBetween(0, 30));

        $checkinTime = (clone $date)->addHours(fake()->numberBetween(8, 10));

        $checkoutTime = (clone $checkinTime)->addHours(fake()->numberBetween(6, 10));

        return [
            'user_id' => User::factory(),
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
