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
        Schema::create('api_logs', function (Blueprint $table) {
            $table->id();
            $table->string('api_type')->comment('DHRU, GSM, custom');
            $table->foreignId('ticket_id')->nullable()->constrained()->nullOnDelete();
            $table->string('endpoint');
            $table->string('method')->default('POST');
            $table->json('request_data')->nullable();
            $table->json('response_data')->nullable();
            $table->integer('response_code')->nullable();
            $table->enum('status', ['success', 'failed', 'pending'])->default('pending');
            $table->text('error_message')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('api_logs');
    }
};
