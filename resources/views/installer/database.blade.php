@extends('installer.layout')

@section('content')
<div class="p-8">
    <h2 class="text-2xl font-bold text-gray-800 mb-6">
        <i class="fas fa-database text-purple-600 mr-2"></i>Database Configuration
    </h2>

    <form method="POST" action="{{ route('installer.database.post') }}">
        @csrf
        <div class="space-y-4 mb-8">
            <div>
                <label class="block font-medium text-gray-700 mb-2">Database Host</label>
                <input type="text" name="db_host" value="{{ old('db_host', '127.0.0.1') }}" 
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent" required>
            </div>

            <div>
                <label class="block font-medium text-gray-700 mb-2">Database Port</label>
                <input type="text" name="db_port" value="{{ old('db_port', '3306') }}" 
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent" required>
            </div>

            <div>
                <label class="block font-medium text-gray-700 mb-2">Database Name</label>
                <input type="text" name="db_name" value="{{ old('db_name', 'repair_service') }}" 
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent" required>
            </div>

            <div>
                <label class="block font-medium text-gray-700 mb-2">Database Username</label>
                <input type="text" name="db_username" value="{{ old('db_username', 'root') }}" 
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent" required>
            </div>

            <div>
                <label class="block font-medium text-gray-700 mb-2">Database Password</label>
                <input type="password" name="db_password" value="{{ old('db_password') }}" 
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                <p class="text-sm text-gray-500 mt-1">Leave blank if no password</p>
            </div>
        </div>

        <div class="flex justify-between">
            <a href="{{ route('installer.requirements') }}" class="px-6 py-3 border border-gray-300 rounded-lg hover:bg-gray-50">
                <i class="fas fa-arrow-left mr-2"></i>Back
            </a>
            <button type="submit" class="px-6 py-3 bg-purple-600 text-white rounded-lg hover:bg-purple-700">
                Test & Continue<i class="fas fa-arrow-right ml-2"></i>
            </button>
        </div>
    </form>
</div>
@endsection
