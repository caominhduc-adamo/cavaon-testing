<?php

namespace App\Http\Controllers\Api\V1;

use App\Booking;
use App\Http\Controllers\Controller;
use App\Http\Requests\ListBookingRequest;
use App\Http\Requests\StoreBookingRequest;
use App\Http\Requests\UpdateBookingRequest;
use App\Http\Resources\BookingResource;
use App\Services\BookingService;

class BookingController extends Controller
{
    /**
     * @var BookingService
     */
    protected $bookingService;

    public function __construct(BookingService $bookingService)
    {
        $this->bookingService = $bookingService;
    }

    public function index(ListBookingRequest $request)
    {
        $bookings = $this->bookingService->listBookings($request->validated(), $request->query());

        return BookingResource::collection($bookings);
    }

    public function store(StoreBookingRequest $request)
    {
        $booking = $this->bookingService->createBooking($request->validated());

        return (new BookingResource($booking))->response()->setStatusCode(201);
    }

    public function show(Booking $booking)
    {
        $booking = $this->bookingService->getBooking($booking);

        return new BookingResource($booking);
    }

    public function update(UpdateBookingRequest $request, Booking $booking)
    {
        $booking = $this->bookingService->updateBooking($booking, $request->validated());

        return new BookingResource($booking);
    }
}
