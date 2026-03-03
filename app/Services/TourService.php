<?php

namespace App\Services;

use App\Booking;
use App\Tour;
use App\TourDate;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TourService
{
    public function listTours(array $validated, array $queryParams = [])
    {
        $search = isset($validated['q']) ? trim($validated['q']) : null;
        $status = isset($validated['status']) ? $validated['status'] : null;
        $perPage = isset($validated['per_page']) ? (int) $validated['per_page'] : 10;

        return Tour::query()
            ->when($search, function ($query) use ($search) {
                $query->where('name', 'like', '%' . $search . '%');
            })
            ->when($status, function ($query) use ($status) {
                $query->where('status', $status);
            })
            ->with(['tourDates' => function ($query) {
                $query->orderBy('start_date')
                    ->where('status', TourDate::STATUS_ENABLED)
                    ->select(['id', 'tour_id', 'start_date', 'end_date', 'status']);
            }])
            ->orderBy('id', 'desc')
            ->paginate($perPage)
            ->appends($queryParams);
    }

    public function getTour(Tour $tour)
    {
        return $tour->load(['tourDates' => function ($query) {
            $query->orderBy('start_date');
        }]);
    }

    public function createTour(array $validated)
    {
        return DB::transaction(function () use ($validated) {
            $tour = Tour::create([
                'name' => $validated['name'],
                'description' => isset($validated['description']) ? $validated['description'] : null,
                'status' => Tour::STATUS_DRAFT,
            ]);

            $this->syncTourDates($tour, isset($validated['tour_dates']) ? $validated['tour_dates'] : []);

            if (isset($validated['status'])) {
                $this->validateStatusTransition($tour, $validated['status']);
                $tour->status = $validated['status'];
                $tour->save();
            }

            return $tour->load(['tourDates' => function ($query) {
                $query->orderBy('start_date');
            }]);
        });
    }

    public function updateTour(Tour $tour, array $validated)
    {
        return DB::transaction(function () use ($tour, $validated) {
            $this->ensureTourNotStale($tour, $validated['updated_at']);

            $tour->fill([
                'name' => $validated['name'],
                'description' => isset($validated['description']) ? $validated['description'] : null,
            ]);
            $tour->save();

            $this->syncTourDates($tour, isset($validated['tour_dates']) ? $validated['tour_dates'] : []);

            if (isset($validated['status']) && $validated['status'] !== $tour->status) {
                $this->validateStatusTransition($tour, $validated['status']);
                $tour->status = $validated['status'];
                $tour->save();
            }

            return $tour->load(['tourDates' => function ($query) {
                $query->orderBy('start_date');
            }]);
        });
    }

    public function publishTour(Tour $tour)
    {
        return DB::transaction(function () use ($tour) {
            if ($tour->status !== Tour::STATUS_PUBLIC) {
                $this->validateStatusTransition($tour, Tour::STATUS_PUBLIC);
                $tour->status = Tour::STATUS_PUBLIC;
                $tour->save();
            }

            return $tour->load(['tourDates' => function ($query) {
                $query->orderBy('start_date');
            }]);
        });
    }

    protected function syncTourDates(Tour $tour, array $tourDates)
    {
        $incomingIds = [];

        foreach ($tourDates as $tourDatePayload) {
            if (isset($tourDatePayload['id'])) {
                $tourDate = $tour->tourDates()->where('id', $tourDatePayload['id'])->first();

                if (!$tourDate) {
                    throw ValidationException::withMessages([
                        'tour_dates' => ['One or more tour dates do not belong to this tour.'],
                    ]);
                }

                $incomingIds[] = (int) $tourDate->id;
                $tourDate->fill([
                    'start_date' => $tourDatePayload['start_date'],
                    'end_date' => isset($tourDatePayload['end_date']) ? $tourDatePayload['end_date'] : null,
                    'status' => isset($tourDatePayload['status']) ? $tourDatePayload['status'] : TourDate::STATUS_ENABLED,
                ]);
                $tourDate->save();

                continue;
            }

            $tour->tourDates()->create([
                'start_date' => $tourDatePayload['start_date'],
                'end_date' => isset($tourDatePayload['end_date']) ? $tourDatePayload['end_date'] : null,
                'status' => isset($tourDatePayload['status']) ? $tourDatePayload['status'] : TourDate::STATUS_ENABLED,
            ]);
        }

        $existingIds = $tour->tourDates()->pluck('id')->map(function ($id) {
            return (int) $id;
        })->all();
        $idsToDelete = array_values(array_diff($existingIds, $incomingIds));

        if (empty($idsToDelete)) {
            return;
        }

        $hasBookingsOnDeletedDates = Booking::query()
            ->whereIn('tour_date_id', $idsToDelete)
            ->exists();

        if ($hasBookingsOnDeletedDates) {
            throw ValidationException::withMessages([
                'tour_dates' => ['Cannot delete tour dates that already have bookings.'],
            ]);
        }

        $tour->tourDates()->whereIn('id', $idsToDelete)->delete();
    }

    protected function validateStatusTransition(Tour $tour, $nextStatus)
    {
        if ($nextStatus === Tour::STATUS_PUBLIC) {
            $hasEnabledDate = $tour->tourDates()
                ->where('status', TourDate::STATUS_ENABLED)
                ->exists();

            if (!$hasEnabledDate) {
                throw ValidationException::withMessages([
                    'status' => ['Tour must have at least one enabled date before publishing.'],
                ]);
            }
        }
    }

    protected function ensureTourNotStale(Tour $tour, $clientUpdatedAt)
    {
        $currentUpdatedAt = $tour->updated_at ? $tour->updated_at->format('Y-m-d H:i:s') : null;
        $requestUpdatedAt = Carbon::parse($clientUpdatedAt)->format('Y-m-d H:i:s');

        if ($currentUpdatedAt !== $requestUpdatedAt) {
            throw ValidationException::withMessages([
                'tour' => ['This tour has been updated by another user. Please refresh and try again.'],
            ]);
        }
    }
}
