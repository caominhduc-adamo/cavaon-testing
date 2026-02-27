<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\ListTourRequest;
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
}
