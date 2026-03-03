<?php

namespace App\Http\Controllers\Api\V1;

use App\Tour;
use App\Http\Controllers\Controller;
use App\Http\Requests\ListTourRequest;
use App\Http\Requests\UpsertTourRequest;
use App\Http\Resources\TourResource;
use App\Services\TourService;

class TourController extends Controller
{
    /**
     * @var TourService
     */
    protected $tourService;

    public function __construct(TourService $tourService)
    {
        $this->tourService = $tourService;
    }

    public function index(ListTourRequest $request)
    {
        $validated = $request->validated();
        $tours = $this->tourService->listTours($validated, $request->query());

        return TourResource::collection($tours);
    }

    public function store(UpsertTourRequest $request)
    {
        $tour = $this->tourService->createTour($request->validated());

        return (new TourResource($tour))->response()->setStatusCode(201);
    }

    public function show(Tour $tour)
    {
        $tour = $this->tourService->getTour($tour);

        return new TourResource($tour);
    }

    public function update(UpsertTourRequest $request, Tour $tour)
    {
        $tour = $this->tourService->updateTour($tour, $request->validated());

        return new TourResource($tour);
    }

    public function publish(Tour $tour)
    {
        $tour = $this->tourService->publishTour($tour);

        return response()->json([
            'message' => 'Tour published successfully.',
            'data' => (new TourResource($tour))->toArray(request()),
        ]);
    }
}
