<?php

namespace Database\Factories;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

class AttendanceFactory extends Factory
{
    protected $model = Attendance::class;

    public function definition()
    {
        $workDate = Carbon::instance($this->faker->dateTimeBetween('-3 months', 'now'));
        $clockIn  = $workDate->copy()->setTime(9, rand(0, 59));
        $clockOut = $workDate->copy()->setTime(18, rand(0, 59));

        return [
            'user_id'   => User::factory(),
            'work_date' => $workDate->toDateString(),
            'clock_in'  => $clockIn,
            'clock_out' => $clockOut,
            'status'    => 'normal',
            'note'      => null,
        ];
    }

}
