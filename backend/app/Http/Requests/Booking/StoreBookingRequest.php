<?php

namespace App\Http\Requests\Booking;

use Illuminate\Foundation\Http\FormRequest;

class StoreBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'service_id' => ['required', 'exists:services,id'],
            'customer_name' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:500'],
            'date' => ['required', 'date', 'after_or_equal:today'],
            'time' => ['required', 'date_format:H:i'],
        ];
    }

    public function messages(): array
    {
        return [
            'service_id.required' => 'Please select a service.',
            'service_id.exists' => 'The selected service does not exist.',
            'customer_name.required' => 'Customer name is required.',
            'address.required' => 'Address is required.',
            'date.required' => 'Booking date is required.',
            'date.after_or_equal' => 'Booking date must be today or in the future.',
            'time.required' => 'Booking time is required.',
            'time.date_format' => 'Time must be in HH:MM format.',
        ];
    }
}
