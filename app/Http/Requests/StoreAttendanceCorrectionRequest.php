<?php

namespace App\Http\Requests;

use Carbon\Carbon;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreAttendanceCorrectionRequest extends FormRequest
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
            'attendance_id' => ['required', 'integer', 'exists:attendance,id'],
            'requested_time_in' => ['nullable', 'date_format:H:i'],
            'requested_time_out' => ['nullable', 'date_format:H:i'],
            'reason' => ['required', 'string', 'max:1000'],
            'supporting_document' => ['nullable', 'file', 'mimes:pdf,doc,docx,jpg,jpeg,png', 'max:2048'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->hasAny(['requested_time_in', 'requested_time_out'])) {
                    return;
                }

                if (! $this->filled('requested_time_in') && ! $this->filled('requested_time_out')) {
                    $validator->errors()->add('requested_time_in', 'Enter a corrected time in, time out, or both.');

                    return;
                }

                if ($this->filled('requested_time_in') && $this->filled('requested_time_out')
                    && Carbon::parse($this->input('requested_time_out'))->lessThanOrEqualTo(Carbon::parse($this->input('requested_time_in')))) {
                    $validator->errors()->add('requested_time_out', 'The corrected time out must be after the corrected time in.');
                }
            },
        ];
    }
}
