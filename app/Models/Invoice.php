<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = ['start_date', 'end_date', 'customer_id', 'total_cost'];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function invoiceDetails()
    {
        return $this->hasMany(InvoiceDetail::class);
    }

    public function invoiceSummaries()
    {
        return $this->hasMany(InvoiceSummary::class);
    }

    public function scopeWithDetailedSummary($query, $invoiceId)
    {
        return $query->with(['customer.users.invoiceSummaries' => function ($q) use ($invoiceId) {
            $q->where('invoice_id', $invoiceId)
                ->select(
                    'user_id',
                    'number_of_registration',
                    'number_of_activation',
                    'number_of_appointment',
                    'highest_cost_event',
                    'cost',
                    'reason_for_invoice'
                );
        }]);
    }
}
