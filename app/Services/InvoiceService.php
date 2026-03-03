<?php

namespace App\Services;

use App\Invoice;

class InvoiceService
{
    public function listInvoices(array $validated, array $queryParams = [])
    {
        $invoiceNumber = isset($validated['invoice_number']) ? trim($validated['invoice_number']) : null;
        $status = isset($validated['status']) ? $validated['status'] : null;
        $perPage = isset($validated['per_page']) ? (int) $validated['per_page'] : 10;

        return Invoice::query()
            ->when($invoiceNumber, function ($query) use ($invoiceNumber) {
                $query->where('invoice_number', 'like', '%' . $invoiceNumber . '%');
            })
            ->when($status, function ($query) use ($status) {
                $query->where('status', $status);
            })
            ->with('booking')
            ->orderBy('id', 'desc')
            ->paginate($perPage)
            ->appends($queryParams);
    }

    public function getInvoice(Invoice $invoice)
    {
        return $invoice->load('booking');
    }

    public function updateInvoice(Invoice $invoice, array $validated)
    {
        $status = $validated['status'];

        $invoice->fill([
            'amount' => $validated['amount'],
            'currency' => strtoupper($validated['currency']),
            'status' => $status,
            'paid_at' => $status === Invoice::STATUS_PAID ? now() : null,
        ]);
        $invoice->save();

        return $invoice->load('booking');
    }
}
