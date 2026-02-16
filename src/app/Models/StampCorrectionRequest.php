<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StampCorrectionRequest extends Model
{
    protected $table = 'stamp_correction_requests';

    protected $fillable = [
        'user_id',
        'attendance_id',
        'request_type',
        'before_value',
        'after_value',
        'reason',
        'status',
    ];

    /**
     * 勤怠とのリレーション
     */
    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }

    /**
     * ユーザーとのリレーション
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
