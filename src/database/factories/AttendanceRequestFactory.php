<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\AttendanceRequest;
use App\Models\User;
use App\Models\Attendance;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AttendanceRequest>
 */
class AttendanceRequestFactory extends Factory
{
    protected $model = AttendanceRequest::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'attendance_id' => Attendance::factory(),
            'checkin_time' => '09:00',
            'checkout_time' => '18:00',
            'remarks' => $this->faker->sentence(),
            'status' => 'pending',
        ];
    }
}
