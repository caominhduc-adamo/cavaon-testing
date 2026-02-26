<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    const STATUS_UNPAID = 'Unpaid';
    const STATUS_PAID = 'Paid';
    const STATUS_CANCELLED = 'Cancelled';

    protected $table = 'invoices';

    protected $fillable = [
        'booking_id',
        'invoice_number',
        'amount',
        'currency',
        'status',
        'issued_at',
        'paid_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'issued_at' => 'datetime',
        'paid_at' => 'datetime',
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class, 'booking_id');
    }
}
