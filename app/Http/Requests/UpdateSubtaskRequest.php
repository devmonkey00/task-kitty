<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSubtaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('subtask'));
    }

    public function rules(): array
    {
        return [
            'is_completed' => ['required', 'boolean'],
        ];
    }
}
