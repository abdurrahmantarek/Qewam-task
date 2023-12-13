<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Session extends Model
{
    use HasFactory;

    protected $fillable = ['event_type', 'event_date', 'user_id'];


    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function invoiceDetail()
    {
        return $this->hasOne(InvoiceDetail::class);
    }
}
