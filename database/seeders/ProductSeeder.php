<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            [
                'name' => 'iPhone 14 Pro Screen',
                'slug' => 'iphone-14-pro-screen',
                'description' => 'Original iPhone 14 Pro OLED screen replacement part',
                'short_description' => 'iPhone 14 Pro replacement screen',
                'price' => 299.00,
                'cost_price' => 180.00,
                'sku' => 'IPH14PRO-SCR-001',
                'category' => 'Screens',
                'stock_quantity' => 10,
                'low_stock_alert' => 3,
                'status' => 'active',
                'is_featured' => true,
            ],
            [
                'name' => 'Samsung S23 Battery',
                'slug' => 'samsung-s23-battery',
                'description' => 'Genuine Samsung Galaxy S23 battery',
                'short_description' => 'Samsung S23 replacement battery',
                'price' => 79.00,
                'cost_price' => 45.00,
                'sku' => 'SAM-S23-BAT-001',
                'category' => 'Batteries',
                'stock_quantity' => 15,
                'low_stock_alert' => 5,
                'status' => 'active',
                'is_featured' => false,
            ],
        ];

        foreach ($products as $product) {
            Product::create($product);
        }
    }
}
