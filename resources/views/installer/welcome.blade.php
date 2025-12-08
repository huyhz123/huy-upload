@extends('installer.layout')

@section('content')
<div class="p-8 text-center">
    <div class="mb-8">
        <i class="fas fa-rocket text-6xl text-purple-600 mb-4"></i>
        <h2 class="text-3xl font-bold text-gray-800 mb-4">Welcome to Repair Service Installer!</h2>
        <p class="text-gray-600 text-lg mb-8">
            This wizard will guide you through the installation process of your Repair Service & File/Course Sales System.
        </p>
    </div>

    <div class="grid md:grid-cols-3 gap-6 mb-8 text-left">
        <div class="border border-purple-200 rounded-lg p-6 hover:shadow-lg transition">
            <i class="fas fa-check-circle text-3xl text-green-500 mb-4"></i>
            <h3 class="font-bold text-lg mb-2">Quick Setup</h3>
            <p class="text-gray-600 text-sm">Install your application in just a few clicks</p>
        </div>
        <div class="border border-purple-200 rounded-lg p-6 hover:shadow-lg transition">
            <i class="fas fa-database text-3xl text-blue-500 mb-4"></i>
            <h3 class="font-bold text-lg mb-2">Database Setup</h3>
            <p class="text-gray-600 text-sm">Automatic database configuration and migration</p>
        </div>
        <div class="border border-purple-200 rounded-lg p-6 hover:shadow-lg transition">
            <i class="fas fa-user-shield text-3xl text-purple-500 mb-4"></i>
            <h3 class="font-bold text-lg mb-2">Admin Account</h3>
            <p class="text-gray-600 text-sm">Create your administrator account</p>
        </div>
    </div>

    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-8 text-left">
        <h4 class="font-bold text-yellow-800 mb-2">
            <i class="fas fa-exclamation-triangle mr-2"></i>Before you begin:
        </h4>
        <ul class="list-disc list-inside text-sm text-yellow-700 space-y-1">
            <li>Make sure you have PHP 8.2+ installed</li>
            <li>Prepare your database credentials (MySQL/PostgreSQL)</li>
            <li>Ensure storage and bootstrap/cache folders are writable</li>
        </ul>
    </div>

    <a href="{{ route('installer.requirements') }}" class="inline-block bg-gradient-to-r from-purple-600 to-purple-800 text-white px-8 py-4 rounded-lg font-bold text-lg hover:shadow-xl transition transform hover:scale-105">
        <i class="fas fa-arrow-right mr-2"></i>Start Installation
    </a>
</div>
@endsection
