<?php
use function Livewire\Volt\layout;

layout('components.layouts.app');
?>

    {{-- Konten Halaman --}}
<main class="flex-1 p-6 lg:p-10">
    <header class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
            Dashboard Perpustakaan
        </h1>
        <p class="mt-1 text-gray-600 dark:text-gray-400">
            Selamat datang! Berikut adalah ringkasan aktivitas perpustakaan Anda.
        </p>
    </header>

    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">

        <div class="p-6 bg-white rounded-lg shadow-md dark:bg-gray-800">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 uppercase dark:text-gray-400">Total Buku</p>
                    <p class="text-3xl font-bold text-gray-900 dark:text-white">1,250</p>
                </div>
                <span class="p-3 bg-blue-100 rounded-full dark:bg-blue-900">
                    <svg class="w-6 h-6 text-blue-600 dark:text-blue-300" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.185 0 4.236.638 6 1.756 1.764-1.118 3.815-1.756 6-1.756 2.185 0 4.236.638 6 1.756V4.262c-.938-.332-1.948-.512-3-.512-2.185 0-4.236.638-6 1.756z" />
                    </svg>
                </span>
            </div>
        </div>

        <div class="p-6 bg-white rounded-lg shadow-md dark:bg-gray-800">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 uppercase dark:text-gray-400">Total Anggota</p>
                    <p class="text-3xl font-bold text-gray-900 dark:text-white">480</p>
                </div>
                <span class="p-3 bg-green-100 rounded-full dark:bg-green-900">
                    <svg class="w-6 h-6 text-green-600 dark:text-green-300" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zM12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0z" />
                    </svg>
                </span>
            </div>
        </div>

        <div class="p-6 bg-white rounded-lg shadow-md dark:bg-gray-800">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 uppercase dark:text-gray-400">Buku Dipinjam</p>
                    <p class="text-3xl font-bold text-gray-900 dark:text-white">120</p>
                </div>
                <span class="p-3 bg-yellow-100 rounded-full dark:bg-yellow-900">
                    <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-300" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" />
                    </svg>
                </span>
            </div>
        </div>

        <div class="p-6 bg-white rounded-lg shadow-md dark:bg-gray-800">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 uppercase dark:text-gray-400">Lewat Batas</p>
                    <p class="text-3xl font-bold text-red-600 dark:text-red-500">15</p>
                </div>
                <span class="p-3 bg-red-100 rounded-full dark:bg-red-900">
                    <svg class="w-6 h-6 text-red-600 dark:text-red-300" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126z" />
                    </svg>
                </span>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 mt-8 lg:grid-cols-3">

        <div class="lg:col-span-1">
            <div class="p-6 bg-white rounded-lg shadow-md dark:bg-gray-800">
                <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">Tindakan Cepat</h3>
                <div class="flex flex-col space-y-3">
                    <button wire:click="bukaModalTambahBuku" class="w-full px-4 py-2 font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800">
                        Tambah Buku Baru
                    </button>
                    <button wire:click="bukaModalTambahAnggota" class="w-full px-4 py-2 font-medium text-white bg-green-600 rounded-lg hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800">
                        Registrasi Anggota
                    </button>
                    <a href="#" class="w-full px-4 py-2 font-medium text-center text-gray-900 bg-gray-100 rounded-lg dark:bg-gray-700 dark:text-white hover:bg-gray-200 dark:hover:bg-gray-600">
                        Proses Peminjaman
                    </a>
                    <a href="#" class="w-full px-4 py-2 font-medium text-center text-gray-900 bg-gray-100 rounded-lg dark:bg-gray-700 dark:text-white hover:bg-gray-200 dark:hover:bg-gray-600">
                        Proses Pengembalian
                    </a>
                </div>
            </div>
        </div>

        <div class="lg:col-span-2">
            <div class="p-6 bg-white rounded-lg shadow-md dark:bg-gray-800">
                <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">Aktivitas Peminjaman Terkini</h3>
                <div class="overflow-x-auto">
                    <table class="w-full min-w-full text-left">
                        <thead class="border-b dark:border-gray-700">
                            <tr class="text-sm text-gray-600 dark:text-gray-400">
                                <th class="py-2">Anggota</th>
                                <th class="py-2">Buku</th>
                                <th class="py-2">Status</th>
                                <th class="py-2">Tanggal</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y dark:divide-gray-700">
                            <tr class="text-gray-900 dark:text-white">
                                <td class="py-3">Budi Santoso</td>
                                <td class="py-3">Dasar-Dasar Laravel 12</td>
                                <td class="py-3">
                                    <span class="px-2 py-1 text-xs font-medium text-blue-800 bg-blue-100 rounded-full dark:bg-blue-900 dark:text-blue-200">
                                        Dipinjam
                                    </span>
                                </td>
                                <td class="py-3 text-sm text-gray-500">1 jam lalu</td>
                            </tr>
                            <tr class="text-gray-900 dark:text-white">
                                <td class="py-3">Ana Maria</td>
                                <td class="py-3">Atomic Habits</td>
                                <td class="py-3">
                                    <span class="px-2 py-1 text-xs font-medium text-green-800 bg-green-100 rounded-full dark:bg-green-900 dark:text-green-200">
                                        Dikembalikan
                                    </span>
                                </td>
                                <td class="py-3 text-sm text-gray-500">3 jam lalu</td>
                            </tr>
                            <tr class="text-gray-900 dark:text-white">
                                <td class="py-3">Rizky P.</td>
                                <td class="py-3">The Pragmatic Programmer</td>
                                <td class="py-3">
                                    <span class="px-2 py-1 text-xs font-medium text-red-800 bg-red-100 rounded-full dark:bg-red-900 dark:text-red-200">
                                        Telat
                                    </span>
                                </td>
                                <td class="py-3 text-sm text-gray-500">1 hari lalu</td>
                            </tr>
                            </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</main>
