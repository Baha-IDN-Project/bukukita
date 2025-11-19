<?php

use App\Models\User;
use App\Models\Book;
use App\Models\Peminjaman;
use App\Models\Category;
use App\Models\Review; // 1. Ditambahkan Model Review
use function Livewire\Volt\layout;
use function Livewire\Volt\with;

// Mengatur layout utama
layout('components.layouts.app');

// Menggunakan 'with' untuk mengambil data
with(function () {
    // 3. Logika untuk data chart (Peminjaman 7 hari terakhir)
    $chartLabels = collect();
    $chartData = collect();
    for ($i = 6; $i >= 0; $i--) {
        $date = now()->subDays($i);
        $chartLabels->push($date->format('M d')); // Label: Nov 18
        $chartData->push(
            Peminjaman::whereDate('created_at', $date->toDateString())->count()
        );
    }

    return [
        'totalMembers' => User::count(),
        'totalBooks' => Book::count(),
        'totalCategories' => Category::count(),
        'totalReviews' => Review::count(), // 2. Ditambahkan Total Reviews
        'totalOnLoan' => Peminjaman::whereNull('tanggal_harus_kembali')->count(),

        // Data untuk tabel aktivitas terkini
        'recentPeminjaman' => Peminjaman::with(['user', 'book'])
                            ->latest()
                            ->take(5)
                            ->get(),

        // Data untuk chart
        'chartLabels' => $chartLabels,
        'chartData' => $chartData,
    ];
});

?>

<main class="flex-1 p-6 lg:p-10">
    <header class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
            Dashboard Perpustakaan
        </h1>
        <p class="mt-1 text-gray-600 dark:text-gray-400">
            Selamat datang! Berikut adalah ringkasan aktivitas perpustakaan Anda.
        </p>
    </header>

    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-5" wire:poll.15s>

        {{-- Widget 1: Total Buku --}}
        <div class="p-6 bg-white rounded-lg shadow-md dark:bg-gray-800">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 uppercase dark:text-gray-400">Buku</p>
                    <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ $totalBooks }}</p>
                </div>
                <span class="p-3 bg-blue-100 rounded-full dark:bg-blue-900">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-book-open-icon lucide-book-open"><path d="M12 7v14"/><path d="M3 18a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1h5a4 4 0 0 1 4 4 4 4 0 0 1 4-4h5a1 1 0 0 1 1 1v13a1 1 0 0 1-1 1h-6a3 3 0 0 0-3 3 3 3 0 0 0-3-3z"/></svg>
                </span>
            </div>
        </div>

        {{-- Widget 2: Total Anggota --}}
        <div class="p-6 bg-white rounded-lg shadow-md dark:bg-gray-800">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 uppercase dark:text-gray-400">Member</p>
                    <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ $totalMembers }}</p>
                </div>
                <span class="p-3 bg-green-100 rounded-full dark:bg-green-900">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-users-icon lucide-users"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><path d="M16 3.128a4 4 0 0 1 0 7.744"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><circle cx="9" cy="7" r="4"/></svg>
                </span>
            </div>
        </div>

        {{-- Widget 3: Buku Dipinjam --}}
        <div class="p-6 bg-white rounded-lg shadow-md dark:bg-gray-800">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 uppercase dark:text-gray-400">Peminjaman</p>
                    <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ $totalOnLoan }}</p>
                </div>
                <span class="p-3 bg-yellow-100 rounded-full dark:bg-yellow-900">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-book-down-icon lucide-book-down"><path d="M12 13V7"/><path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H19a1 1 0 0 1 1 1v18a1 1 0 0 1-1 1H6.5a1 1 0 0 1 0-5H20"/><path d="m9 10 3 3 3-3"/></svg>
                </span>
            </div>
        </div>

        {{-- Widget 4: Total Kategori --}}
        <div class="p-6 bg-white rounded-lg shadow-md dark:bg-gray-800">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 uppercase dark:text-gray-400">Kategori</p>
                    <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ $totalCategories }}</p>
                </div>
                <span class="p-3 bg-purple-100 rounded-full dark:bg-purple-900">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-chart-column-stacked-icon lucide-chart-column-stacked"><path d="M11 13H7"/><path d="M19 9h-4"/><path d="M3 3v16a2 2 0 0 0 2 2h16"/><rect x="15" y="5" width="4" height="12" rx="1"/><rect x="7" y="8" width="4" height="9" rx="1"/></svg>
                </span>
            </div>
        </div>

        {{-- Widget 5: Total Ulasan (BARU) --}}
        <div class="p-6 bg-white rounded-lg shadow-md dark:bg-gray-800">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 uppercase dark:text-gray-400">Ulasan</p>
                    <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ $totalReviews }}</p>
                </div>
                <span class="p-3 bg-pink-100 rounded-full dark:bg-pink-900">
                    {{-- Icon baru untuk ulasan --}}
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-message-square-more-icon lucide-message-square-more"><path d="M22 17a2 2 0 0 1-2 2H6.828a2 2 0 0 0-1.414.586l-2.202 2.202A.71.71 0 0 1 2 21.286V5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2z"/><path d="M12 11h.01"/><path d="M16 11h.01"/><path d="M8 11h.01"/></svg>
                </span>
            </div>
        </div>

    </div>

    <div class="grid grid-cols-1 gap-6 mt-8 lg:grid-cols-3">

        {{-- Panel Grafik Peminjaman (MENGGANTIKAN Tindakan Cepat) --}}
        <div class="lg:col-span-1">
            <div class="p-6 bg-white rounded-lg shadow-md dark:bg-gray-800"
                 {{--
                   Inisialisasi Alpine.js untuk chart.
                   Data diambil dari PHP menggunakan @js()
                   wire:ignore penting agar Livewire tidak merusak canvas
                 --}}
                 wire:ignore
                 x-data
                 x-init="
                    new Chart($refs.loanChart, {
                        type: 'line',
                        data: {
                            labels: @js($chartLabels),
                            datasets: [{
                                label: 'Peminjaman Baru',
                                data: @js($chartData),
                                backgroundColor: 'rgba(59, 130, 246, 0.2)',
                                borderColor: 'rgba(59, 130, 246, 1)',
                                tension: 0.3,
                                fill: true,
                            }]
                        },
                        options: {
                            responsive: true,
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        stepSize: 1 // Hanya tampilkan angka bulat (1, 2, 3... bukan 1.5)
                                    }
                                }
                            },
                            plugins: {
                                legend: {
                                    display: false // Sembunyikan legenda
                                }
                            }
                        }
                    })
                 "
            >
                <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">Peminjaman 7 Hari Terakhir</h3>
                <canvas x-ref="loanChart"></canvas>
            </div>
        </div>

        {{-- Panel Aktivitas Terkini (Dibuat Dinamis) --}}
        <div class="lg:col-span-2" wire:poll.15s> {{-- wire:poll hanya untuk bagian ini --}}
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
                            @forelse ($recentPeminjaman as $peminjaman)
                                <tr classs="text-gray-900 dark:text-white">
                                    <td class="py-3">{{ $peminjaman->user->name ?? 'N/A' }}</td>
                                    <td class="py-3">{{ $peminjaman->book->title ?? 'N/A' }}</td>
                                    <td class="py-3">
                                        @if ($peminjaman->tanggal_harus_kembali)
                                            <span class="px-2 py-1 text-xs font-medium text-green-800 bg-green-100 rounded-full dark:bg-green-900 dark:text-green-200">
                                                Dikembalikan
                                            </span>
                                        @else
                                            <span class="px-2 py-1 text-xs font-medium text-blue-800 bg-blue-100 rounded-full dark:bg-blue-900 dark:text-blue-200">
                                                Dipinjam
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-3 text-sm text-gray-500">
                                        {{ $peminjaman->created_at->diffForHumans() }}
                                    </td>
                                </tr>
                            @empty
                                <tr class="text-gray-900 dark:text-white">
                                    <td colspan="4" class="py-3 text-center text-gray-500 dark:text-gray-400">
                                        Belum ada aktivitas peminjaman.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</main>

{{--
  Tambahkan ini di akhir file,
  atau pastikan layout utama Anda memuat Chart.js
--}}
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@endpush
