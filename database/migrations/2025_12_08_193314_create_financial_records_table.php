<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('financial_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('payment_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('type', ['revenue', 'expense', 'refund'])->default('revenue');
            $table->string('category')->nullable();
            $table->decimal('amount', 15, 2)->default(0);
            $table->decimal('cost', 15, 2)->default(0);
            $table->decimal('profit', 15, 2)->default(0);
            $table->text('description')->nullable();
            $table->date('record_date');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('financial_records');
    }
};
