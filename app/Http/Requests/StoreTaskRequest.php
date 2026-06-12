<?php

namespace App\Http\Requests;

use App\Enums\TaskPriority;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTaskRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        if ($this->assigned_to === '') {
            $this->merge(['assigned_to' => null]);
        }
    }

    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\Task::class);
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'assigned_to' => ['nullable', 'exists:users,id'],
            'priority' => ['nullable', Rule::in(TaskPriority::values())],
            'due_date' => ['nullable', 'date', 'after_or_equal:today'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Please enter a task title.',
            'assigned_to.exists' => 'The selected assignee is invalid.',
            'due_date.after_or_equal' => 'The due date cannot be in the past.',
        ];
    }
}
