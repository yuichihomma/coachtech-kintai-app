<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Attendance;
use App\Models\BreakTime;
use Carbon\Carbon;

class BreakTimeSeeder extends Seeder
{
    public function run()
    {
        $attendances = Attendance::whereHas('user', function ($q) {
    $q->where('role', 'staff');
})->get();

        foreach ($attendances as $attendance) {

    $date = Carbon::parse($attendance->work_date);

    if ($date->isWeekday()) {
        BreakTime::create([
            'attendance_id' => $attendance->id,
            'break_start'   => $date->copy()->setTime(13, 0),
            'break_end'     => $date->copy()->setTime(14, 0),
        ]);
    }
}

    }
}
