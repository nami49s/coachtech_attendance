<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAttendanceRequest extends FormRequest
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
            'checkin_time.required' => '出勤時間は必須です。',
            'checkin_time.date_format' => '出勤時間は「HH:MM」形式で入力してください。',
            'checkout_time.date_format' => '退勤時間は「HH:MM」形式で入力してください。',
            'checkout_time.after' => '退勤時間は出勤時間より後に設定してください。',
            'break_start.*.before' => '休憩開始時間は退勤時間より前に設定してください。',
            'break_end.*.after' => '休憩終了時間は休憩開始時間より後に設定してください。',
            'break_end.*.before' => '休憩終了時間は退勤時間より前に設定してください。',
            'remarks.required' => '備考を記入してください。',
            'remarks.max' => '備考は255文字以内で入力してください。',
        ];
    }
}
