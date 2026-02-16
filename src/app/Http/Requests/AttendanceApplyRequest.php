<?php

namespace App\Http\Requests;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

class AttendanceApplyRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        // 既存フォーム（breaks[0][start/end]）を設計書の項目名へ寄せる
        $this->merge([
            'break_start' => $this->input('break_start', data_get($this->input('breaks'), '0.start')),
            'break_end'   => $this->input('break_end', data_get($this->input('breaks'), '0.end')),
            // 旧キーnoteが来た場合もreasonとして扱う
            'reason'      => $this->input('reason', $this->input('note')),
        ]);
    }

    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'clock_in'  => ['required', 'date_format:H:i'],
            'clock_out' => ['required', 'date_format:H:i', 'after:clock_in'],
            'break_start' => ['nullable', 'date_format:H:i', 'after:clock_in'],
            'break_end'   => ['nullable', 'date_format:H:i', 'after:break_start'],
            'reason' => ['required', 'string', 'max:255'],

        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $attendance = $this->route('attendance');

            if (!$attendance) {
                return;
            }

            $clockOut = Carbon::parse($this->clock_out);

            foreach ($attendance->breaks as $break) {
                if ($break->break_start && $break->break_start->gt($clockOut)) {
                    $validator->errors()->add('clock_out', '休憩時間が不適切な値です');
                }

                if ($break->break_end && $break->break_end->gt($clockOut)) {
                    $validator->errors()->add('clock_out', '休憩時間が不適切な値です');
                }
            }
        });
    }


    public function messages()
    {
        return [
            // ① 出勤・退勤の不整合
            'clock_out.after' => '出勤時間もしくは退勤時間が不適切な値です',

            // ② 休憩時間の不整合
            'break_start.after' => '休憩時間が不適切な値です',
            'break_end.after'   => '休憩時間が不適切な値です',

            // ③ 備考未入力
            'reason.required' => '備考を記入してください',
        ];
    }
}
