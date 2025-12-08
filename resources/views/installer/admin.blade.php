@extends('installer.layout')

@section('content')
<div class="p-8">
    <h2 class="text-2xl font-bold text-gray-800 mb-6">
        <i class="fas fa-user-shield text-purple-600 mr-2"></i>Create Admin Account
    </h2>

    <form method="POST" action="{{ route('installer.admin.post') }}">
        @csrf
        <div class="space-y-4 mb-8">
            <div>
                <label class="block font-medium text-gray-700 mb-2">Full Name</label>
                <input type="text" name="name" value="{{ old('name', 'Admin User') }}" 
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent" required>
            </div>

            <div>
                <label class="block font-medium text-gray-700 mb-2">Email Address</label>
                <input type="email" name="email" value="{{ old('email', 'admin@repair.com') }}" 
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent" required>
            </div>

            <div>
                <label class="block font-medium text-gray-700 mb-2">Password</label>
                <input type="password" name="password" 
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent" required minlength="8">
                <p class="text-sm text-gray-500 mt-1">Minimum 8 characters</p>
            </div>

            <div>
                <label class="block font-medium text-gray-700 mb-2">Confirm Password</label>
                <input type="password" name="password_confirmation" 
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent" required minlength="8">
            </div>
        </div>

        <div class="flex justify-between">
            <a href="{{ route('installer.database') }}" class="px-6 py-3 border border-gray-300 rounded-lg hover:bg-gray-50">
                <i class="fas fa-arrow-left mr-2"></i>Back
            </a>
            <button type="submit" class="px-6 py-3 bg-purple-600 text-white rounded-lg hover:bg-purple-700">
                Continue<i class="fas fa-arrow-right ml-2"></i>
            </button>
        </div>
    </form>
</div>
@endsection
