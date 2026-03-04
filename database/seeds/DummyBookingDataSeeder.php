<?php

use App\Booking;
use App\Invoice;
use App\Passenger;
use App\Tour;
use App\TourDate;
use Faker\Generator as Faker;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class DummyBookingDataSeeder extends Seeder
{
    /**
     * Seed realistic demo data for booking flows.
     *
     * @return void
     */
    public function run()
    {
        /** @var Faker $faker */
        $faker = app(Faker::class);
        $publicTourNames = [
            'Ha Long Bay 3D2N Cruise Experience',
            'Sapa Fansipan Adventure Weekend',
            'Da Nang - Hoi An Heritage Escape',
            'Ninh Binh Trang An Day Explorer',
            'Phu Quoc Island Relax & Snorkel',
            'Hue Imperial City Discovery',
            'Mekong Delta Floating Market Tour',
            'Da Lat Pine Forest Getaway',
        ];
        $draftTourNames = [
            'Con Dao Hidden Beaches Preview',
            'Quy Nhon Coastal Escape Draft',
        ];

        // Pool of passengers used across bookings.
        $passengers = collect();
        for ($i = 1; $i <= 80; $i++) {
            $passengers->push(Passenger::create([
                'first_name' => $faker->firstName,
                'last_name' => $faker->lastName,
                'email' => $faker->unique()->safeEmail,
                'phone' => $faker->phoneNumber,
                'status' => $faker->boolean(85) ? Passenger::STATUS_ENABLED : Passenger::STATUS_DISABLED,
            ]));
        }

        // Public tours can be booked. Draft tours are for UI/testing coverage.
        $publicTours = collect();
        foreach ($publicTourNames as $index => $tourName) {
            $tour = Tour::create([
                'name' => $tourName,
                'description' => $faker->sentence(14),
                'status' => Tour::STATUS_PUBLIC,
            ]);
            $publicTours->push($tour);

            // Keep at least one enabled date per public tour.
            for ($d = 0; $d < 4; $d++) {
                $startDate = Carbon::today()->addDays((($index + 1) * 3) + ($d * 7));
                TourDate::create([
                    'tour_id' => $tour->id,
                    'start_date' => $startDate->toDateString(),
                    'end_date' => $startDate->copy()->addDays(2)->toDateString(),
                    'status' => $d === 3 ? TourDate::STATUS_DISABLED : TourDate::STATUS_ENABLED,
                ]);
            }
        }

        foreach ($draftTourNames as $index => $tourName) {
            $tour = Tour::create([
                'name' => $tourName,
                'description' => $faker->sentence(10),
                'status' => Tour::STATUS_DRAFT,
            ]);

            TourDate::create([
                'tour_id' => $tour->id,
                'start_date' => Carbon::today()->addDays(10 + $index + 1)->toDateString(),
                'end_date' => Carbon::today()->addDays(12 + $index + 1)->toDateString(),
                'status' => TourDate::STATUS_ENABLED,
            ]);
        }

        // Seed bookings only against Public + Enabled dates.
        for ($i = 1; $i <= 120; $i++) {
            $tour = $publicTours->random();
            $tourDate = $tour->tourDates()->where('status', TourDate::STATUS_ENABLED)->inRandomOrder()->first();

            $booking = Booking::create([
                'tour_id' => $tour->id,
                'tour_date_id' => $tourDate->id,
                'reference' => sprintf('BK-DEMO-%06d', $i),
                'status' => $faker->randomElement([
                    Booking::STATUS_SUBMITTED,
                    Booking::STATUS_CONFIRMED,
                    Booking::STATUS_CANCELLED,
                ]),
                'booked_at' => Carbon::now()->subDays($faker->numberBetween(0, 30)),
            ]);

            $selectedPassengers = $passengers
                ->where('status', Passenger::STATUS_ENABLED)
                ->random($faker->numberBetween(1, 4))
                ->pluck('id')
                ->all();

            $booking->passengers()->sync($selectedPassengers);

            $invoiceStatus = $booking->status === Booking::STATUS_CANCELLED
                ? Invoice::STATUS_CANCELLED
                : $faker->randomElement([Invoice::STATUS_UNPAID, Invoice::STATUS_PAID]);

            $issuedAt = Carbon::parse($booking->booked_at)->copy()->addHours($faker->numberBetween(1, 24));
            $paidAt = $invoiceStatus === Invoice::STATUS_PAID
                ? $issuedAt->copy()->addDays($faker->numberBetween(0, 10))
                : null;

            Invoice::create([
                'booking_id' => $booking->id,
                'invoice_number' => sprintf('INV-DEMO-%06d', $i),
                'amount' => $faker->randomFloat(2, 100, 2500),
                'currency' => 'USD',
                'status' => $invoiceStatus,
                'issued_at' => $issuedAt,
                'paid_at' => $paidAt,
            ]);
        }
    }
}
