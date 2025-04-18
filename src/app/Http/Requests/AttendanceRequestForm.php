<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AttendanceRequestForm extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'checkin_time' => 'required|date_format:H:i',
            'checkout_time' => [
                'nullable',
                'date_format:H:i',
                'after:checkin_time',
            ],
            'break_start.*' => 'nullable|date_format:H:i|before:checkout_time',
            'break_end.*' => 'nullable|date_format:H:i|after:break_start.*|before:checkout_time',
            'remarks' => 'required|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'checkout_time.after' => '出勤時間もしくは退勤時間が不適切な値です。',
            'break_start.*.before' => '休憩開始時間は退勤時間より前に設定してください。',
            'break_end.*.before' => '休憩時間が勤務時間外です。',
            'remarks.required' => '備考を記入してください。',
        ];
    }
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $checkin = $this->input('checkin_time');
            $checkout = $this->input('checkout_time');
            $breakStarts = $this->input('break_start', []);
            $breakEnds = $this->input('break_end', []);

            foreach ($breakStarts as $index => $start) {
                if ($start && ($start < $checkin || ($checkout && $start > $checkout))) {
                    $validator->errors()->add("break_start.$index", '休憩時間が勤務時間外です。');
                }
            }

            foreach ($breakEnds as $index => $end) {
                if ($end && ($end < $checkin || ($checkout && $end > $checkout))) {
                    $validator->errors()->add("break_end.$index", '休憩時間が勤務時間外です。');
                }
                if (isset($breakStarts[$index]) && $end < $breakStarts[$index]) {
                    $validator->errors()->add("break_end.$index", '休憩時間が勤務時間外です。');
                }
            }
        });
    }
}
