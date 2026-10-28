# Fieldnotes — Store Order & Inventory Mini-System

A Laravel 12 application for managing products, customers, orders, stock, payments, and printable bills. Blade pages use AJAX forms. Order confirmations are simulated through queued log entries, with no SMTP service required.

## Requirements

- PHP 8.3.33, Composer, and Laravel's required PHP extensions, including PDO SQLite for the default database.
- A browser with JavaScript enabled for AJAX and printing.
- Write access to `storage`, `bootstrap/cache`, and the SQLite database directory.
- Node.js/npm only for optional Vite development. Store pages embed their CSS/JavaScript and run without a Vite build.

Use the same PHP installation for Composer, Artisan, and the queue worker. With XAMPP, add its PHP directory to PATH or use the full PHP executable path.

## Setup

Run commands from the project directory:

```sh
composer install
```

If `.env` does not exist, copy the example configuration using the command for your shell:

```powershell
# Windows PowerShell
Copy-Item .env.example .env
```

```sh
# macOS/Linux
cp .env.example .env
```

Generate the application key:

```sh
php artisan key:generate
```

Create an empty `database/database.sqlite` file if it does not exist. In PowerShell:

```powershell
if (!(Test-Path database/database.sqlite)) { New-Item database/database.sqlite -ItemType File }
```

Check these `.env` values:

```dotenv
APP_URL=http://127.0.0.1:8000
DB_CONNECTION=sqlite
QUEUE_CONNECTION=database
LOW_STOCK_THRESHOLD=5
MAIL_MAILER=log
```

Leave `DB_DATABASE` unset for the default `database/database.sqlite` location, or set an absolute path to another SQLite file. Then create tables and sample data:

```sh
php artisan migrate --seed
```

The seeder adds six products and five customers, including low-stock/out-of-stock products. It creates no orders. Rerunning `php artisan db:seed` adds missing demo records without duplicating or overwriting records with matching product codes/customer emails.

## Run

Start the application:

```sh
php artisan serve
```

Open `http://127.0.0.1:8000`. No login is required for this local demonstration.

In a second terminal, process confirmation jobs:

```sh
php artisan queue:work --tries=3
```

Jobs wait in the database until a worker runs. To process one pending job, use `php artisan queue:work --once`. Simulated confirmations appear in `storage/logs/laravel.log` with the recipient, order number, total, and currency. No email is sent. Restart workers after job-code changes; `php artisan queue:restart` requests a graceful restart.

For Apache/XAMPP hosting, point the virtual host document root at `public` and enable URL rewriting. The Artisan server above is sufficient for local use.

For an existing installation after pulling updates:

```sh
composer install
php artisan migrate
php artisan optimize:clear
```

Do not regenerate an existing application key during routine updates. Restart any long-running queue worker.

## Interface

| Page | Functionality |
| --- | --- |
| `/` or `/overview` | Store totals, recent orders, and stock alerts |
| `/products` | Search, add, and edit product details, pricing, tax, and stock |
| `/customers` | Search/add customers and view order counts and lifetime spend |
| `/orders` | Create orders, pay balances, and generate bills |

Add/Create buttons reset previous entries and errors. Edit opens saved product details. AJAX forms show validation errors without refreshing the page. Order previews calculate subtotal, tax, balance due, and balance to return.

Use **Pay balance** to record another payment on an unpaid or partially paid order. **Generate bill** opens a bill with a **Print bill / Save PDF** button. Saving PDF uses the browser's print destinations; there is no server-side PDF generator.

## APIs

Base URL: `http://127.0.0.1:8000`. Send `Accept: application/json`, and `Content-Type: application/json` for JSON bodies. API routes are stateless and require no CSRF token; browser form routes retain CSRF protection.

### Create order

`POST /api/orders`

```json
{
  "email": "customer@example.com",
  "name": "Example Customer",
  "items": [{"product_id": 1, "quantity": 2}],
  "paid_amount": 40
}
```

Use a product ID from your catalog with sufficient stock. Alternatively, supply an existing `customer_id` instead of `email` and `name`. `paid_amount` is optional and defaults to zero. HTTP 201 returns `message` and `data`, including the customer, items, totals, and payment balances.

### Customer history

`GET /api/customers/orders?email=customer%40example.com`

HTTP 200 returns `customer` and a `data` array of orders, newest first, with item/product details. A known customer without orders gets an empty list. An unknown customer gets 404.

### Low-stock products

`GET /api/products/low-stock?threshold=5`

HTTP 200 returns `threshold` and a `data` array. Stock less than or equal to the threshold qualifies. Omit the parameter to use `LOW_STOCK_THRESHOLD` from `.env`, default 5. Allowed thresholds are integers from 0 to 1,000,000; zero returns out-of-stock products. After changing cached configuration, run `php artisan config:clear`.

Validation failures return HTTP 422 with `message` and field-keyed `errors`. Missing resources return 404. These demonstration endpoints have no authentication; use sample customer data.

## Assumptions and design decisions

### Customer identity

- Email identifies customers for API orders. An existing email reuses that customer without renaming them. An unknown email creates a customer within the order transaction; failure rolls back that customer too.
- Without `customer_id`, both `email` and `name` are required. If both identity formats are supplied and valid, `customer_id` takes precedence.
- Email is not explicitly lowercased; matching follows database comparison rules.

### Prices, tax, and inventory

- Currency is INR (₹). Switching currency symbols did not exchange-rate-convert seeded numeric prices.
- Prices exclude tax. Each line subtotal is unit price × quantity, rounded to two decimals. Its tax is rounded separately. Order subtotal and tax sum those line values; grand total is their sum.
- Database amounts use decimal columns. Existing PHP calculations use floating-point arithmetic with explicit rounding; follow-up payment addition uses integer paise.
- Quantities must be positive whole numbers. Duplicate product lines are rejected; increase the existing line quantity instead. The API discards unselected product rows before validation, preserving its existing behavior. The browser order endpoint validates every submitted row.
- Order creation deducts stock only if the whole transaction succeeds. Backorders, cancellations/restocking, discounts, shipping, and returns are outside scope.
- “Below the low-stock threshold” is interpreted inclusively: stock equal to the threshold also qualifies. API thresholds are configurable; existing dashboard/product indicators keep their five-unit threshold.

### Concurrent writes

- `OrderService` saves the order, item records, and stock deductions in a transaction. A failure rolls back all writes.
- Products are locked in ID order with `lockForUpdate()` on databases supporting row locks.
- SQLite ignores row locks. It reserves its writer before stock/customer reads, using a five-second busy timeout, configured `IMMEDIATE` mode, and up to five transaction attempts. On PHP versions where Laravel ignores the mode setting, a no-row UPDATE acquires the reservation without changing stored values.
- This serializes SQLite order/payment writes. Persistent contention can still exhaust wait/retry limits. Row-locking databases support greater write concurrency; their behavior has not been integration-tested here.
- A test starts two independent PHP processes against an isolated SQLite file with one unit left. Exactly one order succeeds; the other returns a 422 stock error, with only one order/item/confirmation job created.

### Payments

- Initial payments can be zero, partial, exact, or greater than the total. Balance due is `max(total - paid_amount, 0)`; balance to return is `max(paid_amount - total, 0)`.
- Follow-up payments must be positive, have at most two decimals, and not exceed the remaining balance. Settled orders reject further payments.
- Status is derived from amounts. Settled orders display **Completed** in the action column and bill; the payment-status column uses **Paid**.
- Payments accumulate in `paid_amount`. No separate payment ledger, gateway, refunds, or payment-receipt history is implemented. Balance to return is informational; cash returned is not separately tracked.
- Existing orders receive the payment migration's default zero amount; historical payments are not inferred.

### Queued confirmations

- `SendOrderConfirmation` implements `ShouldQueue` and dispatches after commit. Rolled-back orders do not queue a confirmation.
- The database queue avoids Redis/SMTP dependencies. The job simulates email through a log entry and allows up to three attempts.
- Balance payments do not send another order confirmation. Queue processing is not an exactly-once delivery guarantee; a retry after a worker interruption can repeat a log entry.

### Bills and presentation

- Bills use saved order unit prices, tax percentages, and totals, so later catalog price changes do not alter billed amounts.
- Customer details and product names/codes come from current related records; they are not historical snapshots. Later payments appear when a bill is reopened.
- Bill numbers use order IDs. These are printable order summaries without a separate invoice sequence, GST registration details, or tax-invoice compliance workflow.
- The overview revenue chart and percentage comparison are illustrative, not calculated sales reports. Store totals and order lists use database records.

### Application structure

| Location | Responsibility |
| --- | --- |
| `app/Http/Requests` | Product, customer, order, payment, history, and threshold validation |
| `app/Services/OrderService.php` | Transactional ordering, customer resolution, stock/payment updates, and confirmation dispatch |
| `app/Http/Controllers` | Request coordination, page data, and HTML/JSON responses |
| `app/Models` | Relationships, casts, and derived payment balances |
| `app/Jobs/SendOrderConfirmation.php` | Queued confirmation simulation |
| `resources/views/layouts/store.blade.php` | Shared design, modals, and AJAX behavior |
| `resources/views/store` | Separate pages and printable bill |
| `routes/web.php`, `routes/api.php` | Browser and stateless API endpoints |
| `database/migrations`, `database/seeders` | Schema, constraints, and sample data |

Pages share one layout for a consistent design. AJAX responses replace the active page section with server-rendered HTML, keeping totals and rows consistent with Blade rendering. Product editing, payments, and bills extend the core assignment. Authentication, roles, pagination, and production deployment configuration are outside the current scope.

## Tests

```sh
php artisan test
```

Normal tests use an in-memory SQLite database. The concurrent-order test creates a temporary SQLite file and launches two PHP processes, then cleans up its files. It requires child-process permissions and a writable system temporary directory.

Coverage includes AJAX forms, validation, tax/stock calculations, insufficient stock, email customer resolution/rollback, history, configurable low stock, payments, confirmation queue processing/rollback, simultaneous last-unit orders, and printable bills using saved pricing.

The latest verified suite has **31 passing tests**. Tests verify server responses and print markup; check browser print layout and printer output manually as well.

## Submission artifacts

The brief also requests a shared private repository, screenshots of actual AI prompts used, and a 5–10 minute walkthrough narrated by the developer. Place prompt screenshots in `/prompts` or link/embed them here when available. Screenshots and the recording are separate submission materials; this README does not claim they have been provided.
