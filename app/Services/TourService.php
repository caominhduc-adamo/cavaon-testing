<?php

namespace App\Services;

use App\Tour;
use App\TourDate;

class TourService
{
    public function listTours(array $validated, array $queryParams = [])
    {
        $search = isset($validated['q']) ? trim($validated['q']) : null;
        $perPage = isset($validated['per_page']) ? (int) $validated['per_page'] : 10;

        return Tour::query()
            ->where('status', Tour::STATUS_PUBLIC)
            ->when($search, function ($query) use ($search) {
                $query->where('name', 'like', '%' . $search . '%');
            })
            ->whereHas('tourDates', function ($query) {
                $query->where('status', TourDate::STATUS_ENABLED);
            })
            ->with(['tourDates' => function ($query) {
                $query->where('status', TourDate::STATUS_ENABLED)
                    ->orderBy('start_date')
                    ->select(['id', 'tour_id', 'start_date', 'end_date', 'status']);
            }])
            ->orderBy('name')
            ->paginate($perPage)
            ->appends($queryParams);
    }
}
