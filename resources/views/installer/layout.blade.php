<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Installation Wizard - Repair Service</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen">
    <div class="gradient-bg py-6">
        <div class="container mx-auto px-4">
            <div class="flex items-center justify-between text-white">
                <h1 class="text-2xl font-bold">
                    <i class="fas fa-tools mr-2"></i>
                    Repair Service Installer
                </h1>
                <div class="text-sm">Version 1.0</div>
            </div>
        </div>
    </div>

    <div class="container mx-auto px-4 py-8">
        <div class="max-w-4xl mx-auto">
            @if(session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    {{ session('error') }}
                </div>
            @endif

            @if($errors->any())
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <ul class="list-disc list-inside">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                @yield('content')
            </div>
        </div>
    </div>

    <div class="container mx-auto px-4 py-8 text-center text-gray-600 text-sm">
        <p>&copy; {{ date('Y') }} Repair Service System. All rights reserved.</p>
    </div>

    @stack('scripts')
</body>
</html>
