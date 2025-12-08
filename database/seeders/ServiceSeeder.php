<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Service;
use Illuminate\Support\Str;

class ServiceSeeder extends Seeder
{
    public function run(): void
    {
        $services = [
            [
                'name' => 'iPhone Screen Repair',
                'slug' => 'iphone-screen-repair',
                'description' => 'Professional iPhone screen replacement service with genuine parts. Quick turnaround time.',
                'short_description' => 'iPhone screen replacement with genuine parts',
                'price' => 150.00,
                'cost_price' => 80.00,
                'category' => 'Screen Repair',
                'estimated_time' => 60,
                'status' => 'active',
                'is_featured' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Samsung Battery Replacement',
                'slug' => 'samsung-battery-replacement',
                'description' => 'Original Samsung battery replacement. Restore your phone battery life.',
                'short_description' => 'Samsung battery replacement service',
                'price' => 80.00,
                'cost_price' => 40.00,
                'category' => 'Battery',
                'estimated_time' => 45,
                'status' => 'active',
                'is_featured' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Phone Unlock Service',
                'slug' => 'phone-unlock-service',
                'description' => 'Unlock your phone from any carrier. Fast and reliable service via DHRU API.',
                'short_description' => 'Phone unlock service',
                'price' => 50.00,
                'cost_price' => 25.00,
                'category' => 'Unlock',
                'estimated_time' => 30,
                'status' => 'active',
                'is_featured' => false,
                'sort_order' => 3,
                'api_type' => 'DHRU',
            ],
        ];

        foreach ($services as $service) {
            Service::create($service);
        }
    }
}
