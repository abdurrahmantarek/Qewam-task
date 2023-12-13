<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Customer\ListInvoiceRequest;
use App\Models\Customer;
use App\Services\Api\Customer\InvoiceService;
use App\Traits\ResponseHandler;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Response;

class InvoiceController extends Controller
{
    use ResponseHandler;
    protected $invoiceService;

    public function __construct(InvoiceService $invoiceService) {
        $this->invoiceService = $invoiceService;
    }

    public function show($invoiceId)
    {
        $invoiceData = $this->invoiceService->getInvoiceDetails($invoiceId);
        return $this->successResponse($invoiceData);
    }

    public function store(ListInvoiceRequest $request) {
        $payload = $request->validated();

        return DB::transaction(function () use ($payload) {
            $customer = Customer::with('users.sessions')->findOrFail($payload['customer_id']);

            if ($this->invoiceService->checkInvoiceOverlap($payload['customer_id'], $payload['start_date'], $payload['end_date'])) {
                return $this->errorResponse('The selected date range conflicts with an existing invoice. Please adjust the start and end dates to avoid overlap.', Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $invoice = $this->invoiceService->createOrUpdateInvoice($customer->id, $payload['start_date'], $payload['end_date']);
            $invoiceTotalCost = $this->invoiceService->calculateInvoiceTotalCost($customer, $payload, $invoice);

            $invoice->total_cost = $invoiceTotalCost;
            $invoice->save();

            return $this->successResponse($invoice, 201);
        });
    }
}
