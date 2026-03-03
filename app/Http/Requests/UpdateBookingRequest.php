<?php

namespace App\Http\Requests;

use App\Booking;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBookingRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'tour_id' => ['required', 'integer', Rule::exists('tours', 'id')],
            'tour_date_id' => ['required', 'integer', Rule::exists('tour_dates', 'id')],
            'passenger_ids' => ['required', 'array', 'min:1'],
            'passenger_ids.*' => ['required', 'integer', 'distinct', Rule::exists('passengers', 'id')],
            'status' => ['required', Rule::in([
                Booking::STATUS_SUBMITTED,
                Booking::STATUS_CONFIRMED,
                Booking::STATUS_CANCELLED,
            ])],
        ];
    }
}
