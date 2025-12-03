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
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Edu+NSW+ACT+Cursive:wght@400..700&family=Poiret+One&display=swap" rel="stylesheet">
    
    <!-- Alpine.js -->
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    
    <!-- Custom Fonts CSS (must be last) -->
    <link href="{{ asset('css/custom-fonts.css') }}" rel="stylesheet">
    
    <!-- BladewindUI CSS -->
    <link href="{{ asset('vendor/bladewind/css/animate.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('vendor/bladewind/css/bladewind-ui.min.css') }}" rel="stylesheet" />

    <!-- App assets via Vite (loads Tailwind and your custom CSS/JS) -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <script src="https://tinylytics.app/embed/ZqiXX112qeb4C8pdyGQg.js" defer></script>
    
    @stack('styles')
</head>
<body class="antialiased flex flex-col h-screen">
    <main class="flex-grow">
        @yield('content')
    </main>
    
    <script src='https://storage.ko-fi.com/cdn/scripts/overlay-widget.js'></script>
    <script>
    kofiWidgetOverlay.draw('sparklane', {
        'type': 'floating-chat',
        'floating-chat.donateButton.text': 'Support Me',
        'floating-chat.donateButton.background-color': '#00bfa5',
        'floating-chat.donateButton.text-color': '#fff'
    });
    </script>

    <footer class="bg-gray-100 py-4 mt-auto">
        <div class="container text-right text-gray-600 text-sm pr-5" style="line-height: 46px;">
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
