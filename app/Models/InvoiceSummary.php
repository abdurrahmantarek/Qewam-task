<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceSummary extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'invoice_id', 'number_of_registration', 'number_of_activation', 'number_of_appointment', 'highest_cost_event', 'cost', 'reason_for_invoice'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }
}
