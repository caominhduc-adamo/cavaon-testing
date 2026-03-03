<?php

namespace App\Http\Requests;

use App\Passenger;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ListPassengerRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'q' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', Rule::in([Passenger::STATUS_ENABLED, Passenger::STATUS_DISABLED])],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
        ];
    }
}
