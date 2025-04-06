<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;

class AttendancesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::all()->each(function ($user) {
            for ($i = 0; $i < 10; $i++) {
                $date = Carbon::today()->subDays($i)->toDateString();

                if (!Attendance::where('user_id', $user->id)->where('date', $date)->exists()) {
                    Attendance::factory()->create([
                        'user_id' => $user->id,
                        'date' => $date,
                    ]);
                }
            }
        });
    }
}
