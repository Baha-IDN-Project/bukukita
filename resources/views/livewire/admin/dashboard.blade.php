<?php

use function Livewire\Volt\layout;

?>

<div>
    {{-- Bagian Header Halaman --}}
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Admin Dashboard') }}
        </h2>
    </x-slot>

    {{-- Konten Halaman --}}
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h1>Selamat Datang, Admin!</h1>
                    <p>Ini adalah halaman dashboard khusus untuk Admin.</p>
                </div>
            </div>
        </div>
    </div>
</div>
