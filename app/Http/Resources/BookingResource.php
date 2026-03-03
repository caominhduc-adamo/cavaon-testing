<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BookingResource extends JsonResource
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
            'tour_id' => $this->tour_id,
            'tour_date_id' => $this->tour_date_id,
            'reference' => $this->reference,
            'status' => $this->status,
            'booked_at' => $this->booked_at ? $this->booked_at->toDateTimeString() : null,
            'updated_at' => $this->updated_at ? $this->updated_at->toDateTimeString() : null,
            'tour' => $this->whenLoaded('tour', function () {
                return [
                    'id' => $this->tour->id,
                    'name' => $this->tour->name,
                    'status' => $this->tour->status,
                ];
            }),
            'tour_date' => $this->whenLoaded('tourDate', function () {
                return [
                    'id' => $this->tourDate->id,
                    'start_date' => $this->tourDate->start_date ? $this->tourDate->start_date->toDateString() : null,
                    'end_date' => $this->tourDate->end_date ? $this->tourDate->end_date->toDateString() : null,
                    'status' => $this->tourDate->status,
                ];
            }),
            'invoice' => $this->whenLoaded('invoice', function () {
                return [
                    'id' => $this->invoice->id,
                    'invoice_number' => $this->invoice->invoice_number,
                    'amount' => $this->invoice->amount,
                    'currency' => $this->invoice->currency,
                    'status' => $this->invoice->status,
                    'issued_at' => $this->invoice->issued_at ? $this->invoice->issued_at->toDateTimeString() : null,
                    'paid_at' => $this->invoice->paid_at ? $this->invoice->paid_at->toDateTimeString() : null,
                ];
            }),
            'passengers' => PassengerResource::collection($this->whenLoaded('passengers')),
        ];
    }
}
