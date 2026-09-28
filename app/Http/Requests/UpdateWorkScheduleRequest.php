<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateWorkScheduleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->role === 'admin';
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100', Rule::unique('work_schedules', 'name')->ignore($this->route('workSchedule'))],
            'days' => ['required', 'array', 'min:1'],
            'days.*' => ['in:monday,tuesday,wednesday,thursday,friday,saturday,sunday'],
            'time_in' => ['required', 'date_format:H:i'],
            'time_out' => ['required', 'date_format:H:i', 'after:time_in'],
            'overtime_start' => ['required', 'date_format:H:i', 'after:time_out'],
            'overtime_minimum_minutes' => ['required', 'integer', 'min:1', 'max:480'],
            'overtime_maximum_minutes' => ['required', 'integer', 'min:1', 'max:480', 'gte:overtime_minimum_minutes'],
        ];
    }
}
