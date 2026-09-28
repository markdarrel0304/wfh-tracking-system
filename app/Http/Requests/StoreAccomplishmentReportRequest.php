<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreAccomplishmentReportRequest extends FormRequest
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
            'date' => ['required', 'date'],
            'accomplishment_report_id' => ['nullable', 'integer', 'exists:accomplishment_reports,id'],
            'daily_task_id' => ['nullable', 'integer', 'exists:daily_tasks,id'],
            'title' => ['required_without:daily_task_id', 'nullable', 'string', 'max:150'],
            'category' => ['nullable', 'string', 'in:development,documentation,design,testing,meeting,other'],
            'progress_status' => ['nullable', 'string', 'in:completed,in-progress,blocked'],
            'summary' => ['required', 'string', 'max:500'],
            'blockers' => ['nullable', 'string', 'max:1000'],
            'next_steps' => ['nullable', 'string', 'max:1000'],
            'submission_action' => ['nullable', 'string', 'in:draft,submit'],
            'attachments' => ['nullable', 'array', 'max:5'],
            'attachments.*' => ['file', 'mimes:pdf,doc,docx,xls,xlsx,csv,ppt,pptx,jpg,jpeg,png,zip', 'max:10240'],
        ];
    }
}
