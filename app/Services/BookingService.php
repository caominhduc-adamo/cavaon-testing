<?php

namespace App\Services;

use App\Booking;
use App\Invoice;
use App\Tour;
use App\TourDate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class BookingService
{
    public function listBookings(array $validated, array $queryParams = [])
    {
        $reference = isset($validated['reference']) ? trim($validated['reference']) : null;
        $status = isset($validated['status']) ? $validated['status'] : null;
        $perPage = isset($validated['per_page']) ? (int) $validated['per_page'] : 10;

        return Booking::query()
            ->when($reference, function ($query) use ($reference) {
                $query->where('reference', 'like', '%' . $reference . '%');
            })
            ->when($status, function ($query) use ($status) {
                $query->where('status', $status);
            })
            ->with(['tour', 'tourDate', 'passengers', 'invoice'])
            ->orderBy('id', 'desc')
            ->paginate($perPage)
            ->appends($queryParams);
    }

    public function getBooking(Booking $booking)
    {
        return $booking->load(['tour', 'tourDate', 'passengers', 'invoice']);
    }

    public function createBooking(array $validated)
    {
        return DB::transaction(function () use ($validated) {
            $tour = Tour::query()->findOrFail($validated['tour_id']);
            $tourDate = TourDate::query()->findOrFail($validated['tour_date_id']);

            $this->validateBookingBusinessRules($tour, $tourDate);

            $booking = Booking::create([
                'tour_id' => $tour->id,
                'tour_date_id' => $tourDate->id,
                'reference' => $this->generateReference(),
                'status' => Booking::STATUS_SUBMITTED,
                'booked_at' => now(),
            ]);

            $booking->passengers()->sync($validated['passenger_ids']);

            $booking->invoice()->create([
                'invoice_number' => $this->generateInvoiceNumber(),
                'amount' => 0,
                'currency' => 'USD',
                'status' => Invoice::STATUS_UNPAID,
                'issued_at' => now(),
            ]);

            return $booking->load(['tour', 'tourDate', 'passengers', 'invoice']);
        });
    }

    public function updateBooking(Booking $booking, array $validated)
    {
        return DB::transaction(function () use ($booking, $validated) {
            $tour = Tour::query()->findOrFail($validated['tour_id']);
            $tourDate = TourDate::query()->findOrFail($validated['tour_date_id']);

            $this->validateBookingBusinessRules($tour, $tourDate);

            $booking->fill([
                'tour_id' => $tour->id,
                'tour_date_id' => $tourDate->id,
                'status' => isset($validated['status']) ? $validated['status'] : $booking->status,
            ]);
            $booking->save();

            $booking->passengers()->sync($validated['passenger_ids']);

            return $booking->load(['tour', 'tourDate', 'passengers', 'invoice']);
        });
    }

    protected function validateBookingBusinessRules(Tour $tour, TourDate $tourDate)
    {
        if ($tour->status !== Tour::STATUS_PUBLIC) {
            throw ValidationException::withMessages([
                'tour_id' => ['Tour must be Public to accept bookings.'],
            ]);
        }

        if ((int) $tourDate->tour_id !== (int) $tour->id) {
            throw ValidationException::withMessages([
                'tour_date_id' => ['Selected tour date does not belong to the selected tour.'],
            ]);
        }

        if ($tourDate->status !== TourDate::STATUS_ENABLED) {
            throw ValidationException::withMessages([
                'tour_date_id' => ['Tour date must be Enabled to accept bookings.'],
            ]);
        }
    }

    protected function generateReference()
    {
        do {
            $reference = 'BK-' . now()->format('Ymd') . '-' . Str::upper(Str::random(6));
        } while (Booking::query()->where('reference', $reference)->exists());

        return $reference;
    }

    protected function generateInvoiceNumber()
    {
        do {
            $invoiceNumber = 'INV-' . now()->format('Ymd') . '-' . Str::upper(Str::random(6));
        } while (Invoice::query()->where('invoice_number', $invoiceNumber)->exists());

        return $invoiceNumber;
    }
}
