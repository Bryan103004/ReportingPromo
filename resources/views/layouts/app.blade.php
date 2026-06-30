<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap">

        <!-- Load Vite Client -->
        <script type="module" src="http://localhost:5173/@vite/client"></script>

        <!-- Load CSS Tailwind -->
        <link rel="stylesheet" href="http://localhost:5173/resources/css/app.css">

        <!-- Load JS -->
        <script type="module" src="http://localhost:5173/resources/js/app.js"></script>
    </head>
    <body class="font-sans antialiased">
        <div class="flex min-h-screen flex-col bg-gray-100">
            @include('layouts.navigation')

            <!-- Page Heading -->
            @isset($header)
                <header class="bg-white shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <!-- Page Content -->
            <main class="flex-1">
                @hasSection('content')
                    @yield('content')
                @elseif (isset($slot))
                    {{ $slot }}
                @endif
            </main>

            <footer class="border-t border-gray-200 bg-white">
                <div class="mx-auto max-w-7xl justify-center items-center px-4 py-3 text-center text-xs text-gray-500 sm:px-6 lg:px-8 flex">
                    © {{ now()->year }} {{ config('app.name', 'Tanda Terima') }}
                    <span class="ml-2 text-white">Made by Bryan</span>
                </div>
            </footer>
        </div>
    </body>
</html>
