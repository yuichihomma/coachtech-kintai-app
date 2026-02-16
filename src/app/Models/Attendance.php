<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\BreakTime;
use App\Models\Request;
use Carbon\Carbon;

class Attendance extends Model
{
    use HasFactory;

    const STATUS_NORMAL   = 'normal';
    const STATUS_PENDING  = 'pending';
    const STATUS_APPROVED = 'approved';


    protected $fillable = [
        'user_id',
        'work_date',
        'clock_in',
        'clock_out',
        'note',
        'status',
    ];

    protected $casts = [
        'work_date' => 'date:Y-m-d',
        'clock_in'  => 'datetime',
        'clock_out' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | リレーション
    |--------------------------------------------------------------------------
    */

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function breaks()
    {
        return $this->hasMany(BreakTime::class);
    }

    public function requests()
    {
        return $this->hasMany(Request::class);
    }

    /*
    |--------------------------------------------------------------------------
    | ★ 休憩合計分数（シンプル版）
    |--------------------------------------------------------------------------
    */

    public function getBreakMinutesAttribute()
    {
        return $this->breaks->sum(function ($break) {

            if (!$break->break_start || !$break->break_end) {
                return 0;
            }

            $start = Carbon::parse($break->break_start);
            $end   = Carbon::parse($break->break_end);

            // 日跨ぎ対応
            if ($end->lt($start)) {
                $end->addDay();
            }

            return $start->diffInMinutes($end);

        });
    }

    /*
    |--------------------------------------------------------------------------
    | ★ 表示用：休憩時間（H:i）
    |--------------------------------------------------------------------------
    */

    public function getBreakTimeAttribute()
    {
        $minutes = max(0, (int) $this->break_minutes);

        return floor($minutes / 60)
        . ':'
        . str_pad($minutes % 60, 2, '0', STR_PAD_LEFT);
    }

    /*
    |--------------------------------------------------------------------------
    | ★ 総労働時間
    |--------------------------------------------------------------------------
    */

    public function getTotalTimeAttribute()
    {
        if (!$this->clock_in || !$this->clock_out) {
            return null;
        }

        $clockIn  = Carbon::parse($this->clock_in);
        $clockOut = Carbon::parse($this->clock_out);

        // 日跨ぎ勤務
        if ($clockOut->lt($clockIn)) {
            $clockOut->addDay();
        }

        $workMinutes = $clockIn->diffInMinutes($clockOut);

        $totalMinutes = max(0, $workMinutes - $this->break_minutes);

        return floor($totalMinutes / 60) . ': ' . str_pad($totalMinutes % 60, 2, '0', STR_PAD_LEFT) . '';
    }

    public function stampCorrectionRequest()
    {
        return $this->hasOne(StampCorrectionRequest::class);
    }

}
