<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\BreakTime;
use App\Models\Attendance;
use Carbon\Carbon;

class BreaksTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Attendance::all()->each(function ($attendance) {
            $checkin = Carbon::parse($attendance->date . ' ' . $attendance->checkin_time);
            $checkout = Carbon::parse($attendance->date . ' ' . $attendance->checkout_time);

            $workMinutes = $checkin->diffInMinutes($checkout);

            $breakCount = $workMinutes < 180 ? 1 : ($workMinutes < 360 ? fake()->numberBetween(1, 2) : fake()->numberBetween(1, 3));

            $availableTimeSlots = $this->divideWorkTime($checkin, $checkout, $breakCount);

            foreach ($availableTimeSlots as $index => $timeSlot) {
                $slotStart = $timeSlot['start'];
                $slotEnd = $timeSlot['end'];

                $availableMinutes = $slotStart->diffInMinutes($slotEnd);

                $breakDuration = min(
                    fake()->numberBetween(15, max(16, floor($availableMinutes * 0.4))),
                    60
                );

                $latestPossibleStart = $slotEnd->copy()->subMinutes($breakDuration);

                if ($latestPossibleStart->lte($slotStart)) {
                    $breakStart = $slotStart;
                    $breakEnd = $slotStart->copy()->addMinutes(min(15, $availableMinutes));
                } else {
                    $breakStart = Carbon::instance(
                        fake()->dateTimeBetween($slotStart, $latestPossibleStart)
                    );
                    $breakEnd = $breakStart->copy()->addMinutes($breakDuration);
                }

                BreakTime::create([
                    'attendance_id' => $attendance->id,
                    'break_start' => $breakStart->format('H:i'),
                    'break_end' => $breakEnd->format('H:i'),
                ]);
            }
        });
    }

    /**
     * 勤務時間を指定数の時間枠に分割する
     */
    private function divideWorkTime($checkin, $checkout, $count): array
    {
        $workMinutes = $checkin->diffInMinutes($checkout);
        $slotSize = floor($workMinutes / $count);

        $timeSlots = [];
        $currentTime = $checkin->copy();

        for ($i = 0; $i < $count; $i++) {
            $slotStart = $currentTime->copy();
            $slotEnd = ($i == $count - 1)
                ? $checkout->copy()
                : $currentTime->copy()->addMinutes($slotSize);

            $timeSlots[] = [
                'start' => $slotStart,
                'end' => $slotEnd
            ];

            $currentTime = $slotEnd;
        }

        return $timeSlots;
    }
}