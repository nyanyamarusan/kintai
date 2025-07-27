<?php

namespace App\Http\Requests;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

class RequestRequest extends FormRequest
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
            'reason' => 'required|string',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            try {
                $clockIn = Carbon::createFromFormat('H:i', $this->input('clock_in'));
                $clockOut = Carbon::createFromFormat('H:i', $this->input('clock_out'));
    
                if ($clockIn->gt($clockOut)) {
                    $validator->errors()->add('clock_in', '出勤時間もしくは退勤時間が不適切な値です');
                }
    
                $restTimes = $this->input('rest', []);
                foreach ($restTimes as $index => $rest) {
                    if (!empty($rest['start_time']) && !empty($rest['end_time'])) {
                        $startTime = Carbon::createFromFormat('H:i', $rest['start_time']);
                        $endTime = Carbon::createFromFormat('H:i', $rest['end_time']);
    
                        if ($startTime->lt($clockIn) || $startTime->gt($clockOut)) {
                            $validator->errors()->add("rest.$index.start_time", "休憩時間が不適切な値です");
                        } elseif ($endTime->gt($clockOut)) {
                            $validator->errors()->add("rest.$index.end_time", "休憩時間が不適切な値です");
                        } elseif ($startTime->gt($endTime)) {
                            $validator->errors()->add("rest.$index.start_time", "休憩時間が不適切な値です");
                        }
                    }
                }
    
            } catch (\Exception $e) {
                logger()->warning('時刻パースに失敗しました', [
                    'input' => $this->all(),
                    'error' => $e->getMessage(),
                ]);
            }
        });
    }

    public function messages(): array
    {
        return [
            'reason.required' => '備考を記入してください',
        ];
    }
}
