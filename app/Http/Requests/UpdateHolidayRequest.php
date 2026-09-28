<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateHolidayRequest extends FormRequest
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
            'date' => ['required', 'date_format:Y-m-d', Rule::unique('holidays', 'date')->where(fn ($query) => $query->where('name', $this->input('name')))->ignore($this->route('holiday'))],
            'type' => ['required', Rule::in(['regular', 'special'])],
            'is_recurring' => ['required', 'boolean'],
            'is_active' => ['required', 'boolean'],
        ];
    }
}
