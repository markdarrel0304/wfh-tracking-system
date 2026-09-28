<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDepartmentRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:150'],
            'code' => ['required', 'string', 'max:20', Rule::unique('departments', 'code')->ignore($this->route('department'))],
            'location' => ['required', 'string', 'max:100'],
            'department_head' => ['nullable', 'string', 'max:150'],
            'department_head_count' => ['required', 'integer', 'min:1', 'max:2'],
            'is_active' => ['required', 'boolean'],
        ];
    }
}
