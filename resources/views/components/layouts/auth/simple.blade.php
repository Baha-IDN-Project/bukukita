<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark h-full">
    <head>
        @include('partials.head')
        {{-- Pastikan Tailwind config mendukung warna gray-950, jika tidak gunakan neutral-950 --}}
    </head>
    <body class="min-h-screen bg-gray-950 text-gray-100 antialiased font-sans overflow-x-hidden selection:bg-indigo-500 selection:text-white">

        {{-- 1. GLOBAL BACKGROUND (Diambil dari Referensi) --}}
        <div class="fixed inset-0 z-0">
            {{-- Gambar Background dengan animasi lambat --}}
            <img src="https://images.unsplash.com/photo-1481627834876-b7833e8f5570?q=80&w=2228&auto=format&fit=crop"
                 alt="Background"
                 class="h-full w-full object-cover opacity-40 blur-[2px] transition-transform duration-[60s] hover:scale-110 ease-linear">

            {{-- Overlay Gradients untuk keterbacaan --}}
            <div class="absolute inset-0 bg-gray-950/80 mix-blend-multiply"></div>
            <div class="absolute inset-0 bg-gradient-to-t from-gray-950 via-gray-950/60 to-transparent"></div>
        </div>

        {{-- 2. MAIN CONTENT WRAPPER --}}
        <div class="relative z-10 flex min-h-screen flex-col items-center justify-center p-6 md:p-10">

            <div class="flex w-full max-w-[420px] flex-col gap-6">

                {{-- Logo Section --}}
                <div class="flex flex-col items-center gap-2 text-center">
                    <a href="{{ route('home') }}" class="group flex flex-col items-center gap-3" wire:navigate>
                        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-indigo-500/20 text-indigo-400 ring-1 ring-inset ring-indigo-500/40 shadow-lg shadow-indigo-500/20 transition-all group-hover:bg-indigo-500 group-hover:text-white group-hover:scale-110">
                            <x-app-logo-icon class="size-7 fill-current" />
                        </div>
                        <div class="space-y-0.5">
                            <h1 class="text-xl font-bold tracking-tight text-white">
                                Perpustakaan Digital
                            </h1>
                            <p class="text-xs font-medium text-indigo-300/80 uppercase tracking-widest">
                                Jelajahi Dunia Tanpa Batas
                            </p>
                        </div>
                    </a>
                </div>

                {{-- Slot Container (Card Style dari Referensi) --}}
                <div class="relative overflow-hidden rounded-2xl bg-gray-900/60 backdrop-blur-xl shadow-2xl ring-1 ring-white/10 transition-all hover:ring-white/20 hover:shadow-indigo-500/10">
                    {{-- Decorative Top Gradient Line --}}
                    <div class="absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-indigo-500 to-transparent opacity-50"></div>

                    <div class="p-6 sm:p-8">
                        {{ $slot }}
                    </div>
                </div>

                {{-- Footer Text --}}
                <div class="text-center">
                    <p class="text-xs text-gray-500">
                        &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
                    </p>
                </div>

            </div>
        </div>

        @fluxScripts
    </body>
</html>
