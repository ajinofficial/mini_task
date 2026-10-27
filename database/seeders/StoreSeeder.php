<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Product;
use Illuminate\Database\Seeder;

class StoreSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            ['name' => 'Everyday Ceramic Mug', 'code' => 'HME-001', 'price' => 18.00, 'tax_percentage' => 8.25, 'stock' => 42],
            ['name' => 'Linen Market Tote', 'code' => 'BAG-014', 'price' => 32.00, 'tax_percentage' => 8.25, 'stock' => 18],
            ['name' => 'Cedar & Sage Candle', 'code' => 'HME-026', 'price' => 24.00, 'tax_percentage' => 8.25, 'stock' => 6],
            ['name' => 'Stoneware Serving Bowl', 'code' => 'KIT-008', 'price' => 46.00, 'tax_percentage' => 8.25, 'stock' => 3],
            ['name' => 'Soft Cotton Tea Towel', 'code' => 'KIT-021', 'price' => 14.00, 'tax_percentage' => 8.25, 'stock' => 27],
            ['name' => 'Amber Glass Vase', 'code' => 'DEC-003', 'price' => 38.00, 'tax_percentage' => 8.25, 'stock' => 0],
        ] as $product) {
            Product::firstOrCreate(['code' => $product['code']], $product);
        }

        foreach ([
            ['name' => 'Olivia Chen', 'email' => 'olivia.chen@example.com'],
            ['name' => 'Mateo Rivera', 'email' => 'mateo.rivera@example.com'],
            ['name' => 'Ava Thompson', 'email' => 'ava.thompson@example.com'],
            ['name' => 'Noah Williams', 'email' => 'noah.williams@example.com'],
            ['name' => 'Sofia Patel', 'email' => 'sofia.patel@example.com'],
        ] as $customer) {
            Customer::firstOrCreate(['email' => $customer['email']], $customer);
        }
    }
}
