<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Customer\HomeController;
use App\Http\Controllers\Customer\ServiceController as CustomerServiceController;
use App\Http\Controllers\Customer\FileController as CustomerFileController;
use App\Http\Controllers\Customer\CourseController as CustomerCourseController;
use App\Http\Controllers\Customer\TicketController as CustomerTicketController;
use App\Http\Controllers\Customer\DashboardController as CustomerDashboardController;
use App\Http\Controllers\Customer\CartController;
use App\Http\Controllers\Customer\CheckoutController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\ServiceController as AdminServiceController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\FileController as AdminFileController;
use App\Http\Controllers\Admin\CourseController as AdminCourseController;
use App\Http\Controllers\Admin\TicketController as AdminTicketController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ChatbotController;
use App\Http\Controllers\CICDDashboardController;

// CI/CD Dashboard Routes (Protected by auth + admin role)
Route::middleware(['auth', 'role:admin'])->prefix('cicd')->name('cicd.')->group(function () {
    Route::get('/dashboard', [CICDDashboardController::class, 'index'])->name('dashboard');
    Route::get('/api/data', [CICDDashboardController::class, 'getData'])->name('api.data');
    Route::post('/api/scan', [CICDDashboardController::class, 'triggerScan'])->name('api.scan');
    Route::get('/api/module/{name}', [CICDDashboardController::class, 'getModuleDetails'])->name('api.module');
    Route::get('/api/logs', [CICDDashboardController::class, 'getLogs'])->name('api.logs');
});

// Public Routes
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/services', [CustomerServiceController::class, 'index'])->name('services.index');
Route::get('/services/{slug}', [CustomerServiceController::class, 'show'])->name('services.show');
Route::get('/files', [CustomerFileController::class, 'index'])->name('files.index');
Route::get('/files/{slug}', [CustomerFileController::class, 'show'])->name('files.show');
Route::get('/courses', [CustomerCourseController::class, 'index'])->name('courses.index');
Route::get('/courses/{slug}', [CustomerCourseController::class, 'show'])->name('courses.show');

// Chatbot Routes
Route::post('/chatbot/message', [ChatbotController::class, 'sendMessage'])->name('chatbot.message');

// Auth Routes (Breeze)
require __DIR__.'/auth.php';

// Authenticated Customer Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [CustomerDashboardController::class, 'index'])->name('dashboard');
    
    // Cart & Checkout
    Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
    Route::delete('/cart/{id}', [CartController::class, 'remove'])->name('cart.remove');
    Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
    Route::post('/checkout/process', [CheckoutController::class, 'process'])->name('checkout.process');
    
    // Tickets
    Route::get('/tickets', [CustomerTicketController::class, 'index'])->name('tickets.index');
    Route::get('/tickets/create', [CustomerTicketController::class, 'create'])->name('tickets.create');
    Route::post('/tickets', [CustomerTicketController::class, 'store'])->name('tickets.store');
    Route::get('/tickets/{id}', [CustomerTicketController::class, 'show'])->name('tickets.show');
    
    // Downloads
    Route::get('/downloads', [CustomerFileController::class, 'downloads'])->name('downloads.index');
    Route::get('/download/{token}', [CustomerFileController::class, 'download'])->name('download.file');
});

// Payment Callback Routes
Route::post('/payment/callback/vnpay', [PaymentController::class, 'vnpayCallback'])->name('payment.vnpay.callback');
Route::post('/payment/callback/momo', [PaymentController::class, 'momoCallback'])->name('payment.momo.callback');
Route::post('/payment/callback/paypal', [PaymentController::class, 'paypalCallback'])->name('payment.paypal.callback');

// Admin Routes
Route::middleware(['auth', 'role:admin|staff'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
    
    // CRUD Resources
    Route::resource('services', AdminServiceController::class);
    Route::resource('products', AdminProductController::class);
    Route::resource('files', AdminFileController::class);
    Route::resource('courses', AdminCourseController::class);
    Route::resource('tickets', AdminTicketController::class);
    Route::resource('users', AdminUserController::class);
    
    // Additional Admin Routes
    Route::post('/tickets/{id}/assign', [AdminTicketController::class, 'assign'])->name('tickets.assign');
    Route::post('/tickets/{id}/update-status', [AdminTicketController::class, 'updateStatus'])->name('tickets.updateStatus');
});

// Installer Routes (No middleware - accessible before installation)
Route::prefix('installer')->name('installer.')->group(function () {
    Route::get('/', [App\Http\Controllers\InstallerController::class, 'index'])->name('index');
    Route::get('/requirements', [App\Http\Controllers\InstallerController::class, 'requirements'])->name('requirements');
    Route::get('/database', [App\Http\Controllers\InstallerController::class, 'database'])->name('database');
    Route::post('/database', [App\Http\Controllers\InstallerController::class, 'databasePost'])->name('database.post');
    Route::get('/admin', [App\Http\Controllers\InstallerController::class, 'admin'])->name('admin');
    Route::post('/admin', [App\Http\Controllers\InstallerController::class, 'adminPost'])->name('admin.post');
    Route::get('/install', [App\Http\Controllers\InstallerController::class, 'install'])->name('install');
    Route::post('/process', [App\Http\Controllers\InstallerController::class, 'process'])->name('process');
    Route::get('/complete', [App\Http\Controllers\InstallerController::class, 'complete'])->name('complete');
});
