{{-- File: resources/views/components/layouts/user.blade.php --}}

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        {{-- Asumsi dari file app/sidebar.blade.php --}}
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white dark:bg-zinc-800">

        {{-- 1. Panggil komponen header --}}
        <x-layouts.app.header />

        {{-- 2. Area konten utama --}}
        <flux:main>
            {{--
              Ini adalah bagian PENTING.
              File dashboard.blade.phpa memiliki <x-slot name="header">.
              Kode ini akan mengambil slot itu dan menampilkannya di sini.
            --}}
            @if (isset($header))
                <header class="bg-zinc-50 dark:bg-zinc-900 border-b border-zinc-200 dark:border-zinc-700">
                    <div class="container mx-auto py-4 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endif

            {{-- 3. Ini adalah slot untuk konten utama halaman (isi dashboard Anda) --}}

            {{ $slot }}


        </flux:main>

        @fluxScripts
    </body>
</html>
