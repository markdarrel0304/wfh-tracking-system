<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateDailyTaskRequest extends FormRequest
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
            'title' => ['required', 'string', 'max:150'],
            'task_description' => ['nullable', 'string', 'max:1000'],
            'priority' => ['required', 'in:low,normal,high'],
            'due_time' => ['nullable', 'date_format:H:i'],
            'status' => ['required', 'in:pending,in-progress,done'],
        ];
    }
}
