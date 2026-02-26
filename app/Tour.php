<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Tour extends Model
{
    const STATUS_DRAFT = 'Draft';
    const STATUS_PUBLIC = 'Public';

    protected $table = 'tours';

    protected $fillable = [
        'name',
        'description',
        'status',
    ];

    public function tourDates()
    {
        return $this->hasMany(TourDate::class, 'tour_id');
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class, 'tour_id');
    }
}
