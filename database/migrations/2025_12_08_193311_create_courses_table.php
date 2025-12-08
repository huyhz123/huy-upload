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
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->text('short_description')->nullable();
            $table->decimal('price', 15, 2)->default(0);
            $table->string('category')->nullable();
            $table->string('thumbnail')->nullable();
            $table->string('preview_video')->nullable();
            $table->json('curriculum')->nullable();
            $table->integer('duration_hours')->default(0);
            $table->string('level')->default('beginner');
            $table->enum('status', ['active', 'inactive', 'coming_soon'])->default('active');
            $table->boolean('is_featured')->default(false);
            $table->integer('enrolled_count')->default(0);
            $table->decimal('rating', 3, 2)->default(0);
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
        Schema::dropIfExists('courses');
    }
};
