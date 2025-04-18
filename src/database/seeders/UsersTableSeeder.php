<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Attendance;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $manualUser = User::create([
            'name' => 'テストユーザー',
            'email' => 'test' . time() . '@example.com',
            'password' => Hash::make('password'),
        ]);

        for ($i = 0; $i < 10; $i++) {
            $date = Carbon::today()->subDays($i)->toDateString();

            if (!Attendance::where('user_id', $manualUser->id)->where('date', $date)->exists()) {
                Attendance::factory()->create([
                    'user_id' => $manualUser->id,
                    'date' => $date,
                ]);
            }
        }

        User::factory(10)->create()->each(function ($user) {
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