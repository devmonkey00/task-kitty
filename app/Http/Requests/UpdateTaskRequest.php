<?php

namespace App\Http\Requests;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTaskRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        if ($this->assigned_to === '') {
            $this->merge(['assigned_to' => null]);
        }
    }

    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('task'));
    }

    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'assigned_to' => ['nullable', 'exists:users,id'],
            'status' => ['sometimes', Rule::in(TaskStatus::values())],
            'priority' => ['sometimes', Rule::in(TaskPriority::values())],
            'due_date' => ['nullable', 'date'],
            'position' => ['sometimes', 'integer', 'min:0'],
        ];
    }
}
