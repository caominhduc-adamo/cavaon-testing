<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\ListPassengerRequest;
use App\Http\Requests\UpsertPassengerRequest;
use App\Http\Resources\PassengerResource;
use App\Passenger;
use App\Services\PassengerService;

class PassengerController extends Controller
{
    /**
     * @var PassengerService
     */
    protected $passengerService;

    public function __construct(PassengerService $passengerService)
    {
        $this->passengerService = $passengerService;
    }

    public function index(ListPassengerRequest $request)
    {
        $passengers = $this->passengerService->listPassengers($request->validated(), $request->query());

        return PassengerResource::collection($passengers);
    }

    public function store(UpsertPassengerRequest $request)
    {
        $passenger = $this->passengerService->createPassenger($request->validated());

        return (new PassengerResource($passenger))->response()->setStatusCode(201);
    }

    public function show(Passenger $passenger)
    {
        $passenger = $this->passengerService->getPassenger($passenger);

        return new PassengerResource($passenger);
    }

    public function update(UpsertPassengerRequest $request, Passenger $passenger)
    {
        $passenger = $this->passengerService->updatePassenger($passenger, $request->validated());

        return new PassengerResource($passenger);
    }
}
