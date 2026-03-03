<?php

namespace App\Http\Requests;

use App\Invoice;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateInvoiceRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'amount' => ['required', 'numeric', 'min:0'],
            'currency' => ['required', 'string', 'size:3'],
            'status' => ['required', Rule::in([
                Invoice::STATUS_UNPAID,
                Invoice::STATUS_PAID,
                Invoice::STATUS_CANCELLED,
            ])],
        ];
    }
}
