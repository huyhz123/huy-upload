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
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_number')->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->string('device_name')->nullable();
            $table->string('device_model')->nullable();
            $table->string('imei')->nullable();
            $table->text('issue_description')->nullable();
            $table->text('notes')->nullable();
            $table->decimal('quoted_price', 15, 2)->default(0);
            $table->decimal('final_price', 15, 2)->default(0);
            $table->enum('status', ['pending', 'in_progress', 'waiting_parts', 'completed', 'delivered', 'cancelled'])->default('pending');
            $table->enum('priority', ['low', 'normal', 'high', 'urgent'])->default('normal');
            $table->timestamp('estimated_completion')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->string('api_order_id')->nullable();
            $table->string('api_status')->nullable();
            $table->json('api_response')->nullable();
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
        Schema::dropIfExists('tickets');
    }
};
