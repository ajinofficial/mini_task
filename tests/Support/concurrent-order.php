<?php

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

require __DIR__.'/../../vendor/autoload.php';
$app = require __DIR__.'/../../bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();
config([
    'database.default' => 'sqlite',
    'database.connections.sqlite.url' => null,
    'database.connections.sqlite.database' => $argv[1],
    'queue.default' => 'database',
    'session.driver' => 'array',
    'cache.default' => 'array',
]);
DB::purge('sqlite');
touch($argv[2]);
$deadline = microtime(true) + 15;
while (! file_exists($argv[3])) {
    if (microtime(true) > $deadline) {
        throw new RuntimeException('Concurrent test gate timed out.');
    }
    usleep(10000);
}
// Keep the winning transaction open briefly so the other process must wait.
$paused = false;
DB::listen(function () use (&$paused) {
    if (! $paused && DB::transactionLevel() > 0) {
        $paused = true;
        usleep(200000);
    }
});
$request = Request::create('/api/orders', 'POST', [], [], [], [
    'CONTENT_TYPE' => 'application/json', 'HTTP_ACCEPT' => 'application/json',
], json_encode([
    'customer_id' => (int) $argv[4],
    'items' => [['product_id' => (int) $argv[5], 'quantity' => 1]],
]));
$response = $kernel->handle($request);
echo json_encode(['status' => $response->getStatusCode(), 'body' => json_decode($response->getContent(), true)]);
$kernel->terminate($request, $response);
