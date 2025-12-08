<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Setting;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            ['key' => 'site_name', 'value' => 'Repair Service Pro', 'group' => 'general', 'type' => 'text'],
            ['key' => 'site_description', 'value' => 'Professional device repair and digital sales platform', 'group' => 'general', 'type' => 'textarea'],
            ['key' => 'site_email', 'value' => 'info@repair.com', 'group' => 'general', 'type' => 'email'],
            ['key' => 'site_phone', 'value' => '+84 123 456 789', 'group' => 'general', 'type' => 'text'],
            ['key' => 'default_language', 'value' => 'vi', 'group' => 'localization', 'type' => 'select'],
            ['key' => 'timezone', 'value' => 'Asia/Ho_Chi_Minh', 'group' => 'localization', 'type' => 'select'],
            ['key' => 'currency', 'value' => 'VND', 'group' => 'payment', 'type' => 'select'],
            ['key' => 'enable_chatbot', 'value' => '1', 'group' => 'features', 'type' => 'boolean'],
        ];

        foreach ($settings as $setting) {
            Setting::create($setting);
        }
    }
}
