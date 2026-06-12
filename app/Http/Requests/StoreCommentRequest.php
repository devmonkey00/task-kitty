<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCommentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', [\App\Models\Comment::class, $this->route('task')]);
    }

    public function rules(): array
    {
        return [
            'body' => ['required', 'string', 'min:1', 'max:5000'],
        ];
    }

    public function messages(): array
    {
        return [
            'body.required' => 'Please enter a comment.',
        ];
    }
}
