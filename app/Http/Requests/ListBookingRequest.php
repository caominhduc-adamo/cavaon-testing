<?php

namespace App\Http\Requests;

use App\Booking;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ListBookingRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'reference' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', Rule::in([
                Booking::STATUS_SUBMITTED,
                Booking::STATUS_CONFIRMED,
                Booking::STATUS_CANCELLED,
            ])],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
        ];
    }
}
