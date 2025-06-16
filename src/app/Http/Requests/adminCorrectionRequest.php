<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Carbon\Carbon;

class adminCorrectionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            "work_start" => ["required","date_format:H:i"],
            "work_end" => ["required","date_format:H:i"],
            "work_start_date" => ["required", "date_format:Y-m-d"],
            "work_end_date" => ["required", "date_format:Y-m-d"],
            "rest_start.*" => ["nullable","date_format:H:i"],
            "rest_end.*" => ["nullable","date_format:H:i"],
            "rest_start_date.*" => ["nullable", "date_format:Y-m-d"],
            "rest_end_date.*" => ["nullable", "date_format:Y-m-d"],
            "remarks" => ["required","string"],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $data = $this->all();

            if (empty($data['work_start']) || empty($data['work_end'])) {
                return; // 必須フィールドがなければ、以降の複雑な時間チェックはスキップ
            }

            // 1. 勤務開始時間より勤務終了時間が早くなっていないかのチェック
            $workStartTime = Carbon::parse($data['work_start_date'] . ' ' . $data['work_start']);
            $workEndTime = Carbon::parse($data['work_end_date'] . ' ' . $data['work_end']);

            if ($workEndTime->lte($workStartTime)) {
                $validator->errors()->add('work_end', '出勤時間もしくは退勤時間が不適切な値です');
            }

            // 2. 休憩の開始または終了時間が勤務開始時間と勤務終了時間の間に収まっているかのチェック
            // 休憩が複数ある場合を想定 (配列で送られてくる)
            $restStarts = $data['rest_start'] ?? [];
            $restEnds = $data['rest_end'] ?? [];
            $restStartsDates = $data['rest_start_date'] ?? [];
            $restEndsDates = $data['rest_end_date'] ?? [];


            // 休憩の開始と終了がペアになるようにループ
            foreach ($restStarts as $index => $restStartStr) {
                $restEndStr = $restEnds[$index] ?? null;
                $restStartDateStr = $restStartsDates[$index] ?? null;
                $restEndDateStr = $restEndsDates[$index] ?? null;

                $hasAnyTimeInput = !empty($restStartStr) || !empty($restEndStr);



                if (!$hasAnyTimeInput) {
                    continue; // 次の休憩データへ
                }

                if (empty($restStartStr) || empty($restEndStr)) {
                    $validator->errors()->add('rest_end.' . $index, '休憩開始時間、休憩終了時間両方を入力してください');
                }

                if ($restStartStr && $restEndStr) {
                    if ($restStartDateStr !== null && $restEndDateStr !== null) {
                        $restStartTime = Carbon::parse($restStartDateStr . ' ' . $restStartStr);
                        $restEndTime = Carbon::parse($restEndDateStr . ' ' . $restEndStr);

                        // 休憩終了が休憩開始より前であるか
                        if ($restEndTime->lte($restStartTime)) {
                            $validator->errors()->add('rest_end.' . $index, '休憩開始時間もしくは休憩終了時間が不適切な値です');
                        }

                        // 休憩が勤務時間内に収まっているかのチェック
                        if ($restStartTime->lt($workStartTime) || $restStartTime->gte($workEndTime)) {
                            $validator->errors()->add('rest_start.' . $index, '休憩時間が勤務時間外です');
                        }
                        if ($restEndTime->lte($workStartTime) || $restEndTime->gt($workEndTime)) {
                            $validator->errors()->add('rest_end.' . $index, '休憩時間が勤務時間外です');
                        }
                    }else{
                        $restStartTime = Carbon::parse($data['work_start_date'] . ' ' . $restStartStr);
                        $restEndTime = Carbon::parse($data['work_start_date'] . ' ' . $restEndStr);

                        // 休憩終了が休憩開始より前であるか
                        if ($restEndTime->lte($restStartTime)) {
                            $validator->errors()->add('rest_end.' . $index, '休憩開始時間もしくは休憩終了時間が不適切な値です');
                        }

                        // 休憩が勤務時間内に収まっているかのチェック
                        if ($restStartTime->lt($workStartTime) || $restStartTime->gte($workEndTime)) {
                            $validator->errors()->add('rest_start.' . $index, '休憩時間が勤務時間外です');
                        }
                        if ($restEndTime->lte($workStartTime) || $restEndTime->gt($workEndTime)) {
                            $validator->errors()->add('rest_end.' . $index, '休憩時間が勤務時間外です');
                        }
                    }
                }
            }

        });
    }

    public function messages(): array
    {
        return [

            'work_start.required' => '勤務開始時刻は必須です',
            'work_start.date_format' => '勤務開始時刻は時刻形式(例: 13:00)で入力してください',
            'work_end.required' => '勤務終了時刻は必須です',
            'work_end.date_format' => '勤務終了時刻は時刻形式(例: 13:00)で入力してください',

            'rest_start.*.nullable' => '休憩開始時刻は時刻形式で入力してください (例: 12:00)',
            'rest_end.*.nullable' => '休憩終了時刻は時刻形式で入力してください (例: 13:00)',


            'remarks.required' => '備考を記入してください',
            'remarks.string' => '備考は文字列で入力してください。',
        ];
    }

}
