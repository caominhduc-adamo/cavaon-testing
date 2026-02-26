<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    const STATUS_SUBMITTED = 'Submitted';
    const STATUS_CONFIRMED = 'Confirmed';
    const STATUS_CANCELLED = 'Cancelled';

    protected $table = 'bookings';

    protected $fillable = [
        'tour_id',
        'reference',
        'status',
        'booked_at',
    ];

    protected $casts = [
        'booked_at' => 'datetime',
    ];

    public function tour()
    {
        return $this->belongsTo(Tour::class, 'tour_id');
    }

    public function passengers()
    {
        return $this->belongsToMany(Passenger::class, 'booking_passenger', 'booking_id', 'passenger_id')
            ->withTimestamps();
    }

    public function invoice()
    {
        return $this->hasOne(Invoice::class, 'booking_id');
    }
}
