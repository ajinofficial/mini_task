<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## Store APIs and validation

Send `Accept: application/json` and `Content-Type: application/json` for JSON requests. API routes use Laravel's stateless API middleware; the existing browser forms still use CSRF protection.

- `POST /api/orders` accepts the existing `customer_id` payload or `email` and `name`, with `items` containing `product_id` and `quantity`. Optional `paid_amount` works as before. An existing email reuses the customer without renaming them; an unknown email creates a customer inside the order transaction. Failed orders roll back that new customer. If both identity formats are supplied, `customer_id` takes precedence.
- `GET /api/customers/orders?email=olivia.chen%40example.com` returns the matching customer and their orders, newest first, with item/product details. An existing customer without orders returns an empty list. An unknown email returns 404; invalid email input returns 422.
- `GET /api/products/low-stock?threshold=5` returns products with stock less than or equal to the threshold, including zero stock. Omit the query parameter to use `LOW_STOCK_THRESHOLD` from `.env` (default 5). Invalid thresholds return 422. The existing dashboard's five-unit display behavior is preserved.

Example order payload:

```json
{
  "email": "customer@example.com",
  "name": "Example Customer",
  "items": [{"product_id": 1, "quantity": 2}],
  "paid_amount": 40
}
```

Validation lives in `app/Http/Requests`. Order creation, payment updates, tax calculation, stock deduction, and confirmation dispatch live in `app/Services/OrderService.php`. Existing browser/AJAX responses and calculations are preserved.

Order writes reserve SQLite's writer before reading stock, with a five-second busy timeout and up to five transaction attempts. `IMMEDIATE` mode is configured; on older PHP versions where Laravel ignores that setting, a no-row UPDATE reserves the writer without changing data. MySQL/PostgreSQL continue to use ordered row locks. The concurrency test starts two independent PHP processes against an isolated SQLite file: one buys the last unit and the other receives a 422 stock error; only one order and confirmation job are created.

Run `php artisan test` to verify the existing flows, new APIs, queue processing, and simultaneous last-unit ordering. API validation failures return Laravel's standard 422 JSON errors.

## Running confirmation jobs

Successful order creation queues `App\Jobs\SendOrderConfirmation` after the database transaction commits. Both the web/AJAX and API order endpoints use this job. Failed validation or a rolled back transaction does not send a confirmation.

Use `QUEUE_CONNECTION=database` in `.env` (already configured), run `php artisan migrate`, then start the worker:

```sh
php artisan queue:work --tries=3
```

For a single pending job, use `php artisan queue:work --once`. The job simulates an email by logging `Order confirmation email simulated.` with the order number, customer email, and INR total to the configured Laravel log (normally `storage/logs/laravel.log`). No SMTP connection is used. Jobs wait in the database until a worker runs.

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

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework. You can also check out [Laravel Learn](https://laravel.com/learn), where you will be guided through building a modern Laravel application.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Redberry](https://redberry.international/laravel-development)**
- **[Active Logic](https://activelogic.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
