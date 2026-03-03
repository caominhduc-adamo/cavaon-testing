<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBookingRequest extends FormRequest
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
        ];
    }
}
