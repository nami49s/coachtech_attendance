<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\BreakTime;
use App\Models\Attendance;


class BreaksTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Attendance::all()->each(function ($attendance) {
            BreakTime::factory()->count(fake()->numberBetween(1, 3))->create([
                'attendance_id' => $attendance->id,
            ]);
        });
    }
}
