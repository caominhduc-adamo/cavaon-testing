<?php

namespace Tests\Unit\Services;

use App\Booking;
use App\Invoice;
use App\Passenger;
use App\Services\BookingService;
use App\Tour;
use App\TourDate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class BookingServiceTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @var BookingService
     */
    protected $bookingService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->bookingService = app(BookingService::class);
    }

    public function testCreateBookingCreatesBookingPassengersAndInvoice()
    {
        $tour = $this->createTour(Tour::STATUS_PUBLIC);
        $tourDate = $this->createTourDate($tour->id, TourDate::STATUS_ENABLED);
        $passengerA = $this->createPassenger('john@example.com');
        $passengerB = $this->createPassenger('jane@example.com');

        $booking = $this->bookingService->createBooking([
            'tour_id' => $tour->id,
            'tour_date_id' => $tourDate->id,
            'passenger_ids' => [$passengerA->id, $passengerB->id],
        ]);

        $this->assertNotNull($booking->id);
        $this->assertSame($tour->id, $booking->tour_id);
        $this->assertSame($tourDate->id, $booking->tour_date_id);
        $this->assertSame(Booking::STATUS_SUBMITTED, $booking->status);
        $this->assertNotNull($booking->booked_at);
        $this->assertStringStartsWith('BK-' . now()->format('Ymd') . '-', $booking->reference);

        $this->assertTrue($booking->relationLoaded('tour'));
        $this->assertTrue($booking->relationLoaded('tourDate'));
        $this->assertTrue($booking->relationLoaded('passengers'));
        $this->assertTrue($booking->relationLoaded('invoice'));

        $this->assertEqualsCanonicalizing(
            [$passengerA->id, $passengerB->id],
            $booking->passengers->pluck('id')->all()
        );

        $invoice = $booking->invoice;
        $this->assertNotNull($invoice);
        $this->assertSame(Invoice::STATUS_UNPAID, $invoice->status);
        $this->assertSame('USD', $invoice->currency);
        $this->assertEquals('0.00', (string) $invoice->amount);
        $this->assertNotNull($invoice->issued_at);
        $this->assertStringStartsWith('INV-' . now()->format('Ymd') . '-', $invoice->invoice_number);

        $this->assertSame(1, DB::table('bookings')->count());
        $this->assertSame(1, DB::table('invoices')->count());
        $this->assertSame(2, DB::table('booking_passenger')->count());
    }

    public function testCreateBookingThrowsWhenTourIsNotPublic()
    {
        $tour = $this->createTour(Tour::STATUS_DRAFT);
        $tourDate = $this->createTourDate($tour->id, TourDate::STATUS_ENABLED);
        $passenger = $this->createPassenger('inactive-tour@example.com');

        try {
            $this->bookingService->createBooking([
                'tour_id' => $tour->id,
                'tour_date_id' => $tourDate->id,
                'passenger_ids' => [$passenger->id],
            ]);
            $this->fail('Expected ValidationException was not thrown.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('tour_id', $exception->errors());
            $this->assertSame(
                'Tour must be Public to accept bookings.',
                $exception->errors()['tour_id'][0]
            );
        }

        $this->assertSame(0, DB::table('bookings')->count());
        $this->assertSame(0, DB::table('invoices')->count());
        $this->assertSame(0, DB::table('booking_passenger')->count());
    }

    public function testCreateBookingThrowsWhenTourDateBelongsToDifferentTour()
    {
        $selectedTour = $this->createTour(Tour::STATUS_PUBLIC);
        $differentTour = $this->createTour(Tour::STATUS_PUBLIC);
        $tourDate = $this->createTourDate($differentTour->id, TourDate::STATUS_ENABLED);
        $passenger = $this->createPassenger('mismatch@example.com');

        try {
            $this->bookingService->createBooking([
                'tour_id' => $selectedTour->id,
                'tour_date_id' => $tourDate->id,
                'passenger_ids' => [$passenger->id],
            ]);
            $this->fail('Expected ValidationException was not thrown.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('tour_date_id', $exception->errors());
            $this->assertSame(
                'Selected tour date does not belong to the selected tour.',
                $exception->errors()['tour_date_id'][0]
            );
        }

        $this->assertSame(0, DB::table('bookings')->count());
        $this->assertSame(0, DB::table('invoices')->count());
        $this->assertSame(0, DB::table('booking_passenger')->count());
    }

    public function testCreateBookingThrowsWhenTourDateIsDisabled()
    {
        $tour = $this->createTour(Tour::STATUS_PUBLIC);
        $tourDate = $this->createTourDate($tour->id, TourDate::STATUS_DISABLED);
        $passenger = $this->createPassenger('disabled-date@example.com');

        try {
            $this->bookingService->createBooking([
                'tour_id' => $tour->id,
                'tour_date_id' => $tourDate->id,
                'passenger_ids' => [$passenger->id],
            ]);
            $this->fail('Expected ValidationException was not thrown.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('tour_date_id', $exception->errors());
            $this->assertSame(
                'Tour date must be Enabled to accept bookings.',
                $exception->errors()['tour_date_id'][0]
            );
        }

        $this->assertSame(0, DB::table('bookings')->count());
        $this->assertSame(0, DB::table('invoices')->count());
        $this->assertSame(0, DB::table('booking_passenger')->count());
    }

    protected function createTour($status)
    {
        return Tour::query()->create([
            'name' => 'Test Tour ' . uniqid(),
            'description' => 'Tour description',
            'status' => $status,
        ]);
    }

    protected function createTourDate($tourId, $status)
    {
        return TourDate::query()->create([
            'tour_id' => $tourId,
            'start_date' => now()->addDays(7)->toDateString(),
            'end_date' => now()->addDays(10)->toDateString(),
            'status' => $status,
        ]);
    }

    protected function createPassenger($email)
    {
        return Passenger::query()->create([
            'first_name' => 'Test',
            'last_name' => 'Passenger',
            'email' => $email,
            'phone' => '123456789',
            'status' => Passenger::STATUS_ENABLED,
        ]);
    }
}
