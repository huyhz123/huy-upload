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
        Schema::create('files', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('file_path');
            $table->string('file_type')->nullable();
            $table->bigInteger('file_size')->default(0);
            $table->decimal('price', 15, 2)->default(0);
            $table->string('category')->nullable();
            $table->string('thumbnail')->nullable();
            $table->json('preview_images')->nullable();
            $table->boolean('enable_watermark')->default(true);
            $table->string('watermark_text')->nullable();
            $table->integer('download_limit')->default(3);
            $table->integer('token_expiry_hours')->default(24);
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->boolean('is_featured')->default(false);
            $table->integer('total_downloads')->default(0);
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
        Schema::dropIfExists('files');
    }
};
