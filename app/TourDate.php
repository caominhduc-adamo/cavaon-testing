<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TourDate extends Model
{
    const STATUS_ENABLED = 'Enabled';
    const STATUS_DISABLED = 'Disabled';

    protected $table = 'tour_dates';

    protected $fillable = [
        'tour_id',
        'start_date',
        'end_date',
        'status',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function tour()
    {
        return $this->belongsTo(Tour::class, 'tour_id');
    }
}
