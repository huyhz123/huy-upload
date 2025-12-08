<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\File;

class FileSeeder extends Seeder
{
    public function run(): void
    {
        $files = [
            [
                'name' => 'Complete Phone Repair Guide 2024',
                'slug' => 'complete-phone-repair-guide-2024',
                'description' => 'Comprehensive guide for phone repair technicians. Includes troubleshooting and repair procedures.',
                'file_path' => '/storage/files/phone-repair-guide.pdf',
                'file_type' => 'PDF',
                'file_size' => 15000000,
                'price' => 29.99,
                'category' => 'Guides',
                'enable_watermark' => true,
                'watermark_text' => 'RepairService.com',
                'download_limit' => 3,
                'token_expiry_hours' => 48,
                'status' => 'active',
                'is_featured' => true,
                'total_downloads' => 125,
            ],
            [
                'name' => 'IMEI Database Access Tool',
                'slug' => 'imei-database-tool',
                'description' => 'Professional IMEI checking and database access tool',
                'file_path' => '/storage/files/imei-tool.zip',
                'file_type' => 'ZIP',
                'file_size' => 5000000,
                'price' => 49.99,
                'category' => 'Software',
                'enable_watermark' => false,
                'download_limit' => 5,
                'token_expiry_hours' => 72,
                'status' => 'active',
                'is_featured' => true,
                'total_downloads' => 89,
            ],
        ];

        foreach ($files as $file) {
            File::create($file);
        }
    }
}
