{{-- File: resources/views/components/layouts/user.blade.php --}}

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-zinc-50 text-zinc-800 antialiased dark:bg-zinc-950 dark:text-zinc-200">

        {{-- 1. Panggil komponen header --}}
        <x-layouts.app.header />

        {{--
            CHANGE 2: Flux Main Container
            Menambahkan prop `container` agar otomatis ada max-width dan margin auto.
        --}}
        <flux:main container>

            {{--
                CHANGE 3: Header Slot yang Lebih Bersih
                Menghapus background box/border kaku.
                Membiarkan header menyatu dengan flow halaman tapi tetap memiliki jarak yang tegas.
            --}}
            @if (isset($header))
                <header class="py-6 mb-2">
                    {{--
                       Kita tidak perlu container lagi di sini karena <flux:main>
                       sudah membungkusnya. Cukup styling teks/kontennya saja.
                    --}}
                    <div class="flex items-center justify-between">
                        {{ $header }}
                    </div>
                </header>
            @endif

            {{-- 4. Konten Utama --}}
            {{-- Menambahkan wrapper fade-in agar transisi halaman terasa halus --}}
            <div class="animate-in fade-in slide-in-from-bottom-2 duration-500">
                {{ $slot }}
            </div>

        </flux:main>

        @fluxScripts
    </body>
</html>
