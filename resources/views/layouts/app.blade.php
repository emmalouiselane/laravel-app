<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name', 'Laravel'))</title>
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Alpine.js -->
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    
    <!-- BladewindUI CSS -->
    <link href="{{ asset('vendor/bladewind/css/animate.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('vendor/bladewind/css/bladewind-ui.min.css') }}" rel="stylesheet" />
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            content: [
                "./resources/**/*.blade.php",
                "./vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php",
                "./vendor/mkocansey/bladewind/**/*.php",
                "./vendor/mkocansey/bladewind/resources/views/components/**/*.blade.php"
            ],
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#f0f9f5',
                            100: '#dcfce7',
                            200: '#bbf7d0',
                            300: '#86efac',
                            400: '#4ade80',
                            500: '#22c55e',
                            600: '#16a34a',
                            700: '#15803d',
                            800: '#166534',
                            900: '#14532d',
                        },
                        secondary: '#FDFDFC',
                        dark: '#1b1b18',
                        green: '#22c55e',
                    }
                }
            },
            plugins: [],
        }
    </script>
    
    @stack('styles')
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #FDFDFC;
            color: #1b1b18;
        }
    </style>
</head>
<body class="antialiased">
    @yield('content')
    
    <!-- BladewindUI JS -->
    <script src="{{ asset('vendor/bladewind/js/helpers.js') }}"></script>
    <script src="{{ asset('vendor/bladewind/js/bladewind.js') }}"></script>
    <script>
        // Initialize BladewindUI components
        document.addEventListener('alpine:init', () => {
            // Any BladewindUI component initialization can go here
        });
    </script>
    @stack('scripts')
</body>
</html>
