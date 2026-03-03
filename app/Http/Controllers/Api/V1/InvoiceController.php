<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\ListInvoiceRequest;
use App\Http\Requests\UpdateInvoiceRequest;
use App\Http\Resources\InvoiceResource;
use App\Invoice;
use App\Services\InvoiceService;

class InvoiceController extends Controller
{
    /**
     * @var InvoiceService
     */
    protected $invoiceService;

    public function __construct(InvoiceService $invoiceService)
    {
        $this->invoiceService = $invoiceService;
    }

    public function index(ListInvoiceRequest $request)
    {
        $invoices = $this->invoiceService->listInvoices($request->validated(), $request->query());

        return InvoiceResource::collection($invoices);
    }

    public function show(Invoice $invoice)
    {
        $invoice = $this->invoiceService->getInvoice($invoice);

        return new InvoiceResource($invoice);
    }

    public function update(UpdateInvoiceRequest $request, Invoice $invoice)
    {
        $invoice = $this->invoiceService->updateInvoice($invoice, $request->validated());

        return new InvoiceResource($invoice);
    }
}
