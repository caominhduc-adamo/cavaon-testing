<?php

namespace App\Http\Requests;

use App\Invoice;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ListInvoiceRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'invoice_number' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', Rule::in([
                Invoice::STATUS_UNPAID,
                Invoice::STATUS_PAID,
                Invoice::STATUS_CANCELLED,
            ])],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
        ];
    }
}
