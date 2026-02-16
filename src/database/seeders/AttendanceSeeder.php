<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;

class AttendanceSeeder extends Seeder
{
    public function run()
    {
        $staffs = User::where('role', 'staff')->get();

        $start = now()->subYear()->startOfDay();
        $end   = now()->subDay();


        foreach ($staffs as $staff) {

            $date = $start->copy();

            while ($date->lte($end)) {

                // 平日のみ
                if (!$date->isWeekend()) {

                    Attendance::create([
                        'user_id'   => $staff->id,
                        'work_date' => $date->toDateString(),
                        'clock_in'  => $date->copy()->setTime(9, rand(0, 20)),
                        'clock_out' => $date->copy()->setTime(18, rand(0, 20)),
                        'status'    => 'normal',
                        'note'      => null,
                    ]);
                }

                $date->addDay();
            }
        }
    }
}
