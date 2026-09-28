<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDepartmentRequest extends FormRequest
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
            'code' => ['required', 'string', 'max:20', 'unique:departments,code'],
            'location' => ['required', 'string', 'max:100'],
            'department_head' => ['nullable', 'string', 'max:150'],
            'department_head_count' => ['required', 'integer', 'min:1', 'max:2'],
            'is_active' => ['required', 'boolean'],
        ];
    }
}
