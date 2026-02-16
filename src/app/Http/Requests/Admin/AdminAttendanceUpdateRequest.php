<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class AdminAttendanceUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        // middleware(['auth','admin']) が付いてるので基本trueでOK
        return true;
    }

    public function rules(): array
    {
        return [
            'clock_in'     => ['required', 'date_format:H:i'],
            'clock_out'    => ['required', 'date_format:H:i', 'after:clock_in'],

            'break1_start' => ['nullable', 'date_format:H:i'],
            'break1_end'   => ['nullable', 'date_format:H:i', 'after:break1_start'],

            'break2_start' => ['nullable', 'date_format:H:i'],
            'break2_end'   => ['nullable', 'date_format:H:i', 'after:break2_start'],

            'note'         => ['required', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'clock_in.required'  => '出勤時間もしくは退勤時間が不適切な値です',
            'clock_out.required' => '出勤時間もしくは退勤時間が不適切な値です',
            'clock_out.after'    => '出勤時間もしくは退勤時間が不適切な値です',

            'break1_end.after' => '休憩時間が不適切な値です',
            'break2_end.after' => '休憩時間が不適切な値です',

            'note.required' => '備考を記入してください',
        ];
    }
}
