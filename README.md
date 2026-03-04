<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400"></a></p>

<p align="center">
<a href="https://travis-ci.org/laravel/framework"><img src="https://travis-ci.org/laravel/framework.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://poser.pugx.org/laravel/framework/d/total.svg" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://poser.pugx.org/laravel/framework/v/stable.svg" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://poser.pugx.org/laravel/framework/license.svg" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains over 1500 video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the Laravel [Patreon page](https://patreon.com/taylorotwell).

- **[Vehikl](https://vehikl.com/)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Cubet Techno Labs](https://cubettech.com)**
- **[Cyber-Duck](https://cyber-duck.co.uk)**
- **[British Software Development](https://www.britishsoftware.co)**
- **[Webdock, Fast VPS Hosting](https://www.webdock.io/en)**
- **[DevSquad](https://devsquad.com)**
- [UserInsights](https://userinsights.com)
- [Fragrantica](https://www.fragrantica.com)
- [SOFTonSOFA](https://softonsofa.com/)
- [User10](https://user10.com)
- [Soumettre.fr](https://soumettre.fr/)
- [CodeBrisk](https://codebrisk.com)
- [1Forge](https://1forge.com)
- [TECPRESSO](https://tecpresso.co.jp/)
- [Runtime Converter](http://runtimeconverter.com/)
- [WebL'Agence](https://weblagence.com/)
- [Invoice Ninja](https://www.invoiceninja.com)
- [iMi digital](https://www.imi-digital.de/)
- [Earthlink](https://www.earthlink.ro/)
- [Steadfast Collective](https://steadfastcollective.com/)
- [We Are The Robots Inc.](https://watr.mx/)
- [Understand.io](https://www.understand.io/)
- [Abdel Elrafa](https://abdelelrafa.com)
- [Hyper Host](https://hyper.host)
- [Appoly](https://www.appoly.co.uk)
- [OP.GG](https://op.gg)

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

## Run with Docker (Nginx + PHP 7.2 + MySQL + phpMyAdmin)

### Services

- Laravel app (Nginx + PHP-FPM 7.2): `http://localhost:8000`
- MySQL 5.7: `localhost:33060`
- phpMyAdmin: `http://localhost:8080`

### Start project

1. Build and start containers:

```bash
docker compose up -d --build
```

2. Install PHP dependencies inside container:

```bash
docker compose exec app composer install
```

3. Create environment file (if missing):

```bash
cp .env.example .env
```

4. Update DB values in `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=laravel
DB_PASSWORD=secret
```

5. Generate app key and run migrations:

```bash
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate
```

### Stop project

```bash
docker compose down
```

## Testing

### Booking creation unit tests

Booking creation business logic is covered in:

- `tests/Unit/Services/BookingServiceTest.php`

Covered scenarios:

- Creates booking with `Submitted` status, generated reference, passengers, and invoice.
- Rejects creation when tour is not `Public`.
- Rejects creation when selected tour date belongs to a different tour.
- Rejects creation when selected tour date is not `Enabled`.

Run only booking creation tests:

```bash
./vendor/bin/phpunit tests/Unit/Services/BookingServiceTest.php
```

If using Docker:

```bash
docker compose exec app ./vendor/bin/phpunit tests/Unit/Services/BookingServiceTest.php
```

Run all tests:

```bash
./vendor/bin/phpunit
```

## Booking Module Architecture Notes

### 1) Folder structure and why

I use a layered structure that keeps HTTP concerns, business rules, and data access clearly separated:

- `app/Http/Controllers/Api/*Controller.php`: thin request/response layer.
- `app/Services/*Service.php`: business workflows (booking creation, tour lifecycle, invoice creation).
- `app/*` models (`Booking`, `Tour`, `TourDate`, `Invoice`): Eloquent entities and relationships.
- `resources/js/pages/*.vue`: Vue pages for admin/user flows.
- `tests/Unit/Services/*Test.php`: service-level business logic tests.

Why this shape:
- Controllers stay small and easy to reason about.
- Services centralize business invariants.
- Models remain focused on persistence and relations.
- Frontend is decoupled from backend internals through API contracts.

### 2) Preventing N+1 queries

N+1 is prevented by eager loading related entities in service-layer read/write responses:

- `app/Services/BookingService.php` uses:
  - `listBookings()`: `->with(['tour', 'tourDate', 'passengers', 'invoice'])`
  - `getBooking()`, `createBooking()`, `updateBooking()`: `->load(['tour', 'tourDate', 'passengers', 'invoice'])`
- `app/Services/TourService.php` uses:
  - `listTours()`, `getTour()`, `createTour()`, `updateTour()`, `publishTour()`: eager loads `tourDates` (with filtering/ordering).

This ensures related data is fetched in bounded query counts instead of per-row follow-up queries.

### 3) Atomic booking creation (transaction boundaries)

Booking creation is atomic in `app/Services/BookingService.php`:

- `createBooking()` wraps the full workflow in `DB::transaction(...)`.
- Inside one transaction boundary it does:
  1. Load/validate `Tour` and `TourDate`
  2. Create `Booking`
  3. Sync passengers (`booking_passenger` pivot)
  4. Create `Invoice`
  5. Return hydrated booking object

If any step fails, Laravel rolls back the entire unit of work, preventing partial state (for example, booking exists but invoice missing).

### 4) Scaling to 10,000 bookings/day

At this traffic level, the current design is a solid base; I would scale by hardening hot paths:

- Move non-critical side effects (email, notifications, analytics) to queues/workers.
- Introduce read caching for high-frequency tour/date listing endpoints.
- Add DB read replicas for read-heavy screens while keeping writes on primary.
- Use idempotency keys for booking-create API to handle retries safely.
- Add observability: query-time metrics, slow-query alerts, error rate/SLO dashboards.

### 5) Production readiness improvements

- Standardize structured logging/correlation IDs for request tracing.
- Add API rate limiting and abuse protection at gateway level.

### 6) Key trade-offs and assumptions
- Service-layer orchestration over fat controllers improves maintainability but adds abstraction.
- Eager loading improves latency consistency but may return more data than some consumers need.
- Transactional safety improves correctness but increases lock time on write paths.