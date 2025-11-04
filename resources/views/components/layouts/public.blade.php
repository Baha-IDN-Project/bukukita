<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? config('app.name', 'Bukukita') }}</title>

    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

    <!-- Scripts & Styles (Menggunakan Vite) -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>

<body x-data="layout" x-init="$dispatch('dark-mode:init')" x-bind:class="{ 'dark': dark }"
    class="font-sans antialiased bg-zinc-50 dark:bg-zinc-900 text-gray-900 dark:text-white">

    <div class="min-h-screen flex flex-col">

        <!-- ==== NAVBAR PUBLIK ==== -->
        <nav class="bg-white dark:bg-zinc-800 border-b border-zinc-200 dark:border-zinc-700 shadow-sm sticky top-0 z-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <!-- Logo/Brand -->
                    <div class="flex items-center">
                        <a href="{{ route('home') }}" class="flex-shrink-0 flex items-center gap-2">
                            <svg class="h-8 w-auto text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none"
                                viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18c-2.305 0-4.408.867-6 2.292m0-14.25v14.25" />
                            </svg>
                            <span class="font-semibold text-xl dark:text-white">Bukukita</span>
                        </a>
                    </div>

                    <!-- PERUBAHAN DI SINI: Tombol Kanan (Auth & Dark Mode) -->
                    <div class="flex items-center space-x-4">

                        <!-- Tombol Login & Register -->
                        @if (Route::has('login'))
                            <a href="{{ route('login') }}"
                                class="text-sm font-medium text-gray-700 dark:text-gray-300 hover:text-blue-600 dark:hover:text-blue-400">
                                Login
                            </a>
                        @endif

                        @if (Route::has('register'))
                            <a href="{{ route('register') }}"
                                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                Register
                            </a>
                        @endif

                        <!-- TomBol Toggle Dark Mode DITAMBAHKAN DI SINI -->
                        

                    </div>
                </div>
            </div>
        </nav>

        <!-- ==== KONTEN HALAMAN UTAMA ==== -->
        <main class="flex-grow">
            <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
                {{ $slot }}
            </div>
        </main>

        <!-- ==== FOOTER (Opsional) ==== -->
        <footer class="bg-white dark:bg-zinc-800 border-t border-zinc-200 dark:border-zinc-700 mt-12 py-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center text-gray-500 dark:text-gray-400 text-sm">
                &copy; {{ date('Y') }} Bukukita. All rights reserved.
            </div>
        </footer>

    </div>
    @livewireScripts
</body>

</html>

