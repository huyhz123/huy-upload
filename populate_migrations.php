<?php

// Script để tự động populate tất cả migrations
$migrations = [
    '2025_12_08_193310_create_files_table.php' => <<<'PHP'
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
PHP,

    '2025_12_08_193311_create_courses_table.php' => <<<'PHP'
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
PHP,

    '2025_12_08_193311_create_tickets_table.php' => <<<'PHP'
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
PHP,

    '2025_12_08_193312_create_invoices_table.php' => <<<'PHP'
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('ticket_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('type', ['service', 'product', 'file', 'course'])->default('service');
            $table->json('items')->nullable();
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('tax', 15, 2)->default(0);
            $table->decimal('discount', 15, 2)->default(0);
            $table->decimal('total', 15, 2)->default(0);
            $table->enum('status', ['draft', 'pending', 'paid', 'partially_paid', 'overdue', 'cancelled'])->default('pending');
            $table->timestamp('due_date')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->text('notes')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }
PHP,

    '2025_12_08_193313_create_payments_table.php' => <<<'PHP'
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_id')->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('invoice_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('amount', 15, 2)->default(0);
            $table->string('currency', 3)->default('VND');
            $table->enum('payment_method', ['cash', 'bank_transfer', 'vnpay', 'momo', 'paypal', 'stripe', 'alipay', 'wechat'])->default('cash');
            $table->enum('status', ['pending', 'processing', 'completed', 'failed', 'refunded'])->default('pending');
            $table->string('gateway_transaction_id')->nullable();
            $table->json('gateway_response')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }
PHP,
];

echo "Migration definitions created\n";
