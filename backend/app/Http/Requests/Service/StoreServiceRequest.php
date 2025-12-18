<?php

namespace App\Http\Requests\Service;

use Illuminate\Foundation\Http\FormRequest;

class StoreServiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->isAdmin();
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Service title is required.',
            'title.max' => 'Service title cannot exceed 255 characters.',
            'price.required' => 'Service price is required.',
            'price.numeric' => 'Service price must be a number.',
            'price.min' => 'Service price cannot be negative.',
        ];
    }
}
