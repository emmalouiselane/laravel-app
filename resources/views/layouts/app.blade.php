<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow, noarchive, nosnippet, noodp, noimageindex, notranslate, nocache">
    <title>@yield('title', config('app.name', 'Laravel'))</title>
    
    <!-- Favicon -->
    <link rel="icon" href="{{ asset('images/profile-pic.png') }}" type="image/png">
    <link rel="shortcut icon" href="{{ asset('images/profile-pic.png') }}" type="image/png">
    <link rel="apple-touch-icon" href="{{ asset('images/profile-pic.png') }}">
    
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
                            50: '#f0faf4',
                            100: '#dbf5e6',
                            200: '#b8ebcd',
                            300: '#8adcac',
                            400: '#63d688',
                            500: '#3ac162',
                            600: '#2b9c4e',
                            700: '#247b40',
                            800: '#216136',
                            900: '#1d4f2e',
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

<script src="https://tinylytics.app/embed/ZqiXX112qeb4C8pdyGQg.js" defer></script>
    
    @stack('styles')
</head>
<body class="antialiased flex flex-col h-screen">
    <main class="flex-grow">
        @yield('content')
    </main>
    
    <footer class="bg-gray-100 py-4 mt-auto">
        <div class="container mx-auto px-4 text-center text-gray-600 text-sm">
            Version {{ env('APP_RELEASE', '1.0.0') }}
        </div>
    </footer>
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
    
    <!-- BladewindUI JS -->
    <script src="{{ asset('vendor/bladewind/js/helpers.js') }}"></script>
    <script src="{{ asset('vendor/bladewind/js/bladewind.js') }}"></script>
    
    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        // Initialize BladewindUI components
        document.addEventListener('alpine:init', () => {
            // Any BladewindUI component initialization can go here
        });
    </script>
    @stack('scripts')
</body>
</html>
