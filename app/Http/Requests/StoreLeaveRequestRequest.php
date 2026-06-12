<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreLeaveRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\LeaveRequest::class);
    }

    public function rules(): array
    {
        return [
            'start_date' => ['required', 'date', 'after_or_equal:today'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'reason' => ['required', 'string', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'start_date.after_or_equal' => 'The start date cannot be in the past.',
            'end_date.after_or_equal' => 'The end date must be on or after the start date.',
            'reason.required' => 'Please provide a reason for your leave request.',
        ];
    }
}
