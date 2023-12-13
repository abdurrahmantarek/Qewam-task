<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceDetail extends Model
{
    use HasFactory;

    protected $fillable = ['session_id', 'invoice_id'];

    public function session()
    {
        return $this->belongsTo(Session::class);
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }
}
