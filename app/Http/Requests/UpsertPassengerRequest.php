<?php

namespace App\Http\Requests;

use App\Passenger;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpsertPassengerRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'status' => ['nullable', Rule::in([Passenger::STATUS_ENABLED, Passenger::STATUS_DISABLED])],
            'updated_at' => $this->isMethod('put')
                ? ['required', 'date']
                : ['nullable', 'date'],
        ];
    }
}
