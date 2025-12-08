@extends('installer.layout')

@section('content')
<div class="p-8">
    <h2 class="text-2xl font-bold text-gray-800 mb-6">
        <i class="fas fa-server text-purple-600 mr-2"></i>Server Requirements
    </h2>

    <div class="mb-8">
        <h3 class="font-bold text-lg mb-4">PHP Extensions</h3>
        <div class="space-y-2">
            @foreach($requirements as $requirement => $satisfied)
                <div class="flex items-center justify-between p-3 border rounded {{ $satisfied ? 'bg-green-50 border-green-200' : 'bg-red-50 border-red-200' }}">
                    <span class="font-medium">{{ $requirement }}</span>
                    @if($satisfied)
                        <i class="fas fa-check-circle text-green-600 text-xl"></i>
                    @else
                        <i class="fas fa-times-circle text-red-600 text-xl"></i>
                    @endif
                </div>
            @endforeach
        </div>
    </div>

    <div class="mb-8">
        <h3 class="font-bold text-lg mb-4">Directory Permissions</h3>
        <div class="space-y-2">
            @foreach($permissions as $permission => $satisfied)
                <div class="flex items-center justify-between p-3 border rounded {{ $satisfied ? 'bg-green-50 border-green-200' : 'bg-red-50 border-red-200' }}">
                    <span class="font-medium">{{ $permission }}</span>
                    @if($satisfied)
                        <i class="fas fa-check-circle text-green-600 text-xl"></i>
                    @else
                        <i class="fas fa-times-circle text-red-600 text-xl"></i>
                    @endif
                </div>
            @endforeach
        </div>
    </div>

    <div class="flex justify-between">
        <a href="{{ route('installer.index') }}" class="px-6 py-3 border border-gray-300 rounded-lg hover:bg-gray-50">
            <i class="fas fa-arrow-left mr-2"></i>Back
        </a>
        
        @if(collect($requirements)->every(fn($v) => $v) && collect($permissions)->every(fn($v) => $v))
            <a href="{{ route('installer.database') }}" class="px-6 py-3 bg-purple-600 text-white rounded-lg hover:bg-purple-700">
                Next<i class="fas fa-arrow-right ml-2"></i>
            </a>
        @else
            <button disabled class="px-6 py-3 bg-gray-300 text-gray-500 rounded-lg cursor-not-allowed">
                Fix Requirements First
            </button>
        @endif
    </div>
</div>
@endsection
