<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Process\Process;
use Tests\TestCase;

class ConcurrentOrderTest extends TestCase
{
    public function test_two_simultaneous_orders_for_last_unit_have_one_success_and_one_validation_error(): void
    {
        $directory = sys_get_temp_dir().'/store-concurrency-'.bin2hex(random_bytes(8));
        mkdir($directory);
        $database = $directory.'/test.sqlite';
        touch($database);
        $processes = [];
        try {
            config(['database.default' => 'sqlite', 'database.connections.sqlite.url' => null, 'database.connections.sqlite.database' => $database]);
            DB::purge('sqlite');
            $this->artisan('migrate', ['--force' => true])->assertExitCode(0);
            $customer = Customer::create(['name' => 'Concurrent Customer', 'email' => 'concurrent@example.com']);
            $product = Product::create(['name' => 'Last unit', 'code' => 'LAST', 'price' => 100, 'tax_percentage' => 18, 'stock' => 1]);
            for ($i = 0; $i < 2; $i++) {
                $process = new Process([PHP_BINARY, base_path('tests/Support/concurrent-order.php'), $database,
                    $directory.'/ready'.$i, $directory.'/go', (string) $customer->id, (string) $product->id], base_path());
                $process->setTimeout(25);
                $process->start();
                $processes[] = $process;
            }
            $deadline = microtime(true) + 15;
            while (! file_exists($directory.'/ready0') || ! file_exists($directory.'/ready1')) {
                if (microtime(true) > $deadline) {
                    $this->fail('Order workers did not become ready.');
                }
                usleep(10000);
            }
            touch($directory.'/go');
            $results = [];
            foreach ($processes as $process) {
                $process->wait();
                $this->assertTrue($process->isSuccessful(), $process->getErrorOutput());
                $results[] = json_decode($process->getOutput(), true, 512, JSON_THROW_ON_ERROR);
            }
            $statuses = array_column($results, 'status');
            sort($statuses);
            $this->assertSame([201, 422], $statuses);
            $failure = collect($results)->firstWhere('status', 422);
            $this->assertArrayHasKey('items', $failure['body']['errors']);
            $this->assertEquals(0, $product->fresh()->stock);
            $this->assertEquals(1, Order::count());
            $this->assertDatabaseCount('order_items', 1);
            $this->assertDatabaseCount('jobs', 1);
        } finally {
            foreach ($processes as $process) {
                if ($process->isRunning()) {
                    $process->stop();
                }
            }
            DB::disconnect('sqlite');
            foreach (glob($directory.'/*') as $file) {
                unlink($file);
            }
            rmdir($directory);
        }
    }
}
