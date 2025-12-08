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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->text('short_description')->nullable();
            $table->decimal('price', 15, 2)->default(0);
            $table->decimal('cost_price', 15, 2)->default(0);
            $table->string('sku')->unique()->nullable();
            $table->string('category')->nullable();
            $table->string('image')->nullable();
            $table->json('images')->nullable();
            $table->integer('stock_quantity')->default(0);
            $table->integer('low_stock_alert')->default(5);
            $table->enum('status', ['active', 'inactive', 'out_of_stock'])->default('active');
            $table->boolean('is_featured')->default(false);
            $table->integer('sort_order')->default(0);
            $table->json('meta')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
