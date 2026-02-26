<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Passenger extends Model
{
    const STATUS_ENABLED = 'Enabled';
    const STATUS_DISABLED = 'Disabled';

    protected $table = 'passengers';

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone',
        'status',
    ];

    public function bookings()
    {
        return $this->belongsToMany(Booking::class, 'booking_passenger', 'passenger_id', 'booking_id')
            ->withTimestamps();
    }
}
