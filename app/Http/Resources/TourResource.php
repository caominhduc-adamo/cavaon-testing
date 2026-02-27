<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TourResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'status' => $this->status,
            'tour_dates' => $this->whenLoaded('tourDates', function () {
                return $this->tourDates->map(function ($tourDate) {
                    return [
                        'id' => $tourDate->id,
                        'start_date' => $tourDate->start_date ? $tourDate->start_date->toDateString() : null,
                        'end_date' => $tourDate->end_date ? $tourDate->end_date->toDateString() : null,
                        'status' => $tourDate->status,
                    ];
                });
            }),
        ];
    }
}
