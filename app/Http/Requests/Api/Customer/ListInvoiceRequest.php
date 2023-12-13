<?php

namespace App\Http\Requests\Api\Customer;

use App\Models\Invoice;
use App\Rules\Api\Customer\InvoiceOverlapRule;
use Illuminate\Foundation\Http\FormRequest;

class ListInvoiceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'start_date' => ['required', 'date_format:Y-m-d'],
            'end_date' => 'required|date_format:Y-m-d',
            'customer_id' => 'required|exists:customers,id'
        ];
    }
}
