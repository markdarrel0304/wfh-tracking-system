<?php

namespace App\Http\Requests;

use App\Models\AccomplishmentReport;
use App\Models\DailyTask;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreOutputAttachmentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->employee !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'accomplishment_report_id' => ['required', 'integer', 'exists:accomplishment_reports,id'],
            'daily_task_id' => ['nullable', 'integer', 'exists:daily_tasks,id'],
            'attachments' => ['required', 'array', 'min:1', 'max:5'],
            'attachments.*' => ['required', 'file', 'mimes:pdf,doc,docx,xls,xlsx,csv,ppt,pptx,jpg,jpeg,png,zip', 'max:10240'],
        ];
    }

    /**
     * @return array<int, callable(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->hasAny(['accomplishment_report_id', 'daily_task_id'])) {
                    return;
                }

                $report = AccomplishmentReport::find($this->integer('accomplishment_report_id'));
                $dailyTaskId = $this->integer('daily_task_id');

                if (! $report || $report->employee_id !== $this->user()?->employee?->id) {
                    $validator->errors()->add('accomplishment_report_id', 'Choose one of your own daily reports.');

                    return;
                }

                if (! $dailyTaskId) {
                    return;
                }

                $dailyTask = DailyTask::find($dailyTaskId);

                if (! $dailyTask || $dailyTask->employee_id !== $report->employee_id || ! $dailyTask->date->isSameDay($report->date)) {
                    $validator->errors()->add('daily_task_id', 'Choose a task from the same workday as the selected report.');
                }
            },
        ];
    }
}
