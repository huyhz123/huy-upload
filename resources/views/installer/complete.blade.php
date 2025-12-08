@extends('installer.layout')

@section('content')
<div class="p-8 text-center">
    <div class="mb-8">
        <i class="fas fa-check-circle text-8xl text-green-500 mb-6"></i>
        <h2 class="text-3xl font-bold text-gray-800 mb-4">Installation Completed!</h2>
        <p class="text-gray-600 text-lg mb-8">
            Congratulations! Your Repair Service System has been successfully installed.
        </p>
    </div>

    <div class="bg-gradient-to-r from-purple-50 to-purple-100 border border-purple-200 rounded-lg p-6 mb-8 text-left">
        <h3 class="font-bold text-lg mb-4">
            <i class="fas fa-key text-purple-600 mr-2"></i>Your Admin Credentials:
        </h3>
        <div class="space-y-2 text-gray-700">
            <p><strong>Email:</strong> <code class="bg-white px-2 py-1 rounded">{{ $adminEmail }}</code></p>
            <p><strong>Password:</strong> <code class="bg-white px-2 py-1 rounded">The password you set</code></p>
        </div>
    </div>

    <div class="grid md:grid-cols-2 gap-4 mb-8">
        <div class="border border-gray-200 rounded-lg p-6 text-left hover:shadow-lg transition">
            <i class="fas fa-home text-3xl text-blue-500 mb-3"></i>
            <h4 class="font-bold mb-2">Visit Your Website</h4>
            <p class="text-sm text-gray-600 mb-4">Explore your new website and its features</p>
            <a href="/" class="text-blue-600 hover:underline">Go to Homepage <i class="fas fa-arrow-right ml-1"></i></a>
        </div>

        <div class="border border-gray-200 rounded-lg p-6 text-left hover:shadow-lg transition">
            <i class="fas fa-tachometer-alt text-3xl text-purple-500 mb-3"></i>
            <h4 class="font-bold mb-2">Admin Dashboard</h4>
            <p class="text-sm text-gray-600 mb-4">Manage your services, products, and more</p>
            <a href="/admin/dashboard" class="text-purple-600 hover:underline">Go to Dashboard <i class="fas fa-arrow-right ml-1"></i></a>
        </div>
    </div>

    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 text-left mb-8">
        <h4 class="font-bold text-yellow-800 mb-2">
            <i class="fas fa-lightbulb mr-2"></i>Next Steps:
        </h4>
        <ul class="list-disc list-inside text-sm text-yellow-700 space-y-1">
            <li>Configure payment gateways in settings (VNPay, Momo, PayPal, etc.)</li>
            <li>Setup OpenAI API key for AI Chatbot</li>
            <li>Add your services, products, files, and courses</li>
            <li>Customize site settings and branding</li>
            <li>Configure SMTP for email notifications</li>
        </ul>
    </div>

    <div class="flex gap-4 justify-center">
        <a href="/" class="px-8 py-3 bg-gradient-to-r from-blue-600 to-blue-800 text-white rounded-lg font-bold hover:shadow-xl transition">
            <i class="fas fa-home mr-2"></i>Visit Website
        </a>
        <a href="/admin/dashboard" class="px-8 py-3 bg-gradient-to-r from-purple-600 to-purple-800 text-white rounded-lg font-bold hover:shadow-xl transition">
            <i class="fas fa-tachometer-alt mr-2"></i>Admin Dashboard
        </a>
    </div>
</div>
@endsection
