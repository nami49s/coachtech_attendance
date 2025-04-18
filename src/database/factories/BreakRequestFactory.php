<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\BreakRequest;
use App\Models\AttendanceRequest;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\BreakRequest>
 */
class BreakRequestFactory extends Factory
{
    protected $model = BreakRequest::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'attendance_request_id' => AttendanceRequest::factory(),
            'break_start' => '12:00',
            'break_end' => '13:00',
        ];
    }
}
