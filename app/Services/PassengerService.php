<?php

namespace App\Services;

use App\Passenger;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

class PassengerService
{
    public function listPassengers(array $validated, array $queryParams = [])
    {
        $search = isset($validated['q']) ? trim($validated['q']) : null;
        $status = isset($validated['status']) ? $validated['status'] : null;
        $perPage = isset($validated['per_page']) ? (int) $validated['per_page'] : 10;

        return Passenger::query()
            ->when($search, function ($query) use ($search) {
                $query->where(function ($innerQuery) use ($search) {
                    $innerQuery->where('first_name', 'like', '%' . $search . '%')
                        ->orWhere('last_name', 'like', '%' . $search . '%')
                        ->orWhere('email', 'like', '%' . $search . '%')
                        ->orWhere('phone', 'like', '%' . $search . '%');
                });
            })
            ->when($status, function ($query) use ($status) {
                $query->where('status', $status);
            })
            ->orderBy('id', 'desc')
            ->paginate($perPage)
            ->appends($queryParams);
    }

    public function getPassenger(Passenger $passenger)
    {
        return $passenger;
    }

    public function createPassenger(array $validated)
    {
        return Passenger::create([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => isset($validated['email']) ? $validated['email'] : null,
            'phone' => isset($validated['phone']) ? $validated['phone'] : null,
            'status' => isset($validated['status']) ? $validated['status'] : Passenger::STATUS_ENABLED,
        ]);
    }

    public function updatePassenger(Passenger $passenger, array $validated)
    {
        $this->ensurePassengerNotStale($passenger, $validated['updated_at']);

        $passenger->fill([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => isset($validated['email']) ? $validated['email'] : null,
            'phone' => isset($validated['phone']) ? $validated['phone'] : null,
            'status' => isset($validated['status']) ? $validated['status'] : Passenger::STATUS_ENABLED,
        ]);
        $passenger->save();

        return $passenger;
    }

    protected function ensurePassengerNotStale(Passenger $passenger, $clientUpdatedAt)
    {
        $currentUpdatedAt = $passenger->updated_at ? $passenger->updated_at->format('Y-m-d H:i:s') : null;
        $requestUpdatedAt = Carbon::parse($clientUpdatedAt)->format('Y-m-d H:i:s');

        if ($currentUpdatedAt !== $requestUpdatedAt) {
            throw ValidationException::withMessages([
                'passenger' => ['This passenger has been updated by another user. Please refresh and try again.'],
            ]);
        }
    }
}
