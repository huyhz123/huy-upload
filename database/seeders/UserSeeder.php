<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Admin User
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@repair.com',
            'password' => Hash::make('admin123'),
            'user_type' => 'admin',
            'phone' => '+84 123 456 789',
            'address' => '123 Tech Street',
            'city' => 'Ho Chi Minh',
            'country' => 'Vietnam',
            'language' => 'vi',
            'is_active' => true,
        ]);
        $admin->assignRole('admin');

        // Staff User
        $staff = User::create([
            'name' => 'Staff Member',
            'email' => 'staff@repair.com',
            'password' => Hash::make('staff123'),
            'user_type' => 'staff',
            'phone' => '+84 987 654 321',
            'address' => '456 Service Ave',
            'city' => 'Hanoi',
            'country' => 'Vietnam',
            'language' => 'vi',
            'is_active' => true,
        ]);
        $staff->assignRole('staff');

        // Customer Users
        for ($i = 1; $i <= 10; $i++) {
            $customer = User::create([
                'name' => "Customer $i",
                'email' => "customer$i@example.com",
                'password' => Hash::make('password'),
                'user_type' => 'customer',
                'phone' => '+84 ' . rand(100000000, 999999999),
                'address' => "$i Customer Street",
                'city' => $i % 2 == 0 ? 'Ho Chi Minh' : 'Hanoi',
                'country' => 'Vietnam',
                'language' => 'vi',
                'is_active' => true,
                'balance' => rand(0, 10000000) / 100,
            ]);
            $customer->assignRole('customer');
        }
    }
}
