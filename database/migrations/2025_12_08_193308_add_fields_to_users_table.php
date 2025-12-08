<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone', 20)->nullable()->after('email');
            $table->text('address')->nullable()->after('phone');
            $table->string('city', 100)->nullable()->after('address');
            $table->string('country', 100)->default('Vietnam')->after('city');
            $table->string('avatar')->nullable()->after('country');
            $table->enum('user_type', ['admin', 'staff', 'customer'])->default('customer')->after('avatar');
            $table->boolean('is_active')->default(true)->after('user_type');
            $table->decimal('balance', 15, 2)->default(0)->after('is_active');
            $table->decimal('debt', 15, 2)->default(0)->after('balance');
            $table->string('language', 5)->default('vi')->after('debt');
            $table->timestamp('last_login_at')->nullable()->after('language');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'phone', 'address', 'city', 'country', 'avatar',
                'user_type', 'is_active', 'balance', 'debt',
                'language', 'last_login_at'
            ]);
        });
    }
};
