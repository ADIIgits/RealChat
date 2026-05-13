<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="cloudinary-cloud" content="{{ config('services.cloudinary.cloud_name', '') }}">
    <meta name="cloudinary-preset" content="{{ config('services.cloudinary.upload_preset', '') }}">
    <title>{{ config('app.name', 'TeamChat') }}</title>
    <script>
        window.reverbConfig = {
            key:      '{{ config("broadcasting.connections.reverb.key") }}',
            host:     window.location.hostname,
            port:     {{ env('REVERB_PORT', 8080) }},
            wssPort:  {{ env('REVERB_PORT', 8080) }},
            forceTLS: {{ env('REVERB_SCHEME', 'http') === 'https' ? 'true' : 'false' }},
        };
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/echo.js'])
</head>
<body class="h-full bg-gray-900 text-gray-100 antialiased">
    @isset($header)
    <nav class="bg-gray-800 border-b border-gray-700 px-6 py-4">
        {{ $header }}
    </nav>
    @endisset
    {{ $slot }}
    @stack('scripts')
</body>
</html>
