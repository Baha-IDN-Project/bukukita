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

// 4. Aksi "Tindakan Cepat" dihapus
// $bukaModalTambahBuku = ... (dihapus)
// $bukaModalTambahAnggota = ... (dihapus)

?>

{{--
  Konten Halaman
  'wire:poll' dipindahkan dari <main> ke grid masing-masing
--}}
<main class="flex-1 p-6 lg:p-10">
    <header class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
            Dashboard Perpustakaan
        </h1>
        <p class="mt-1 text-gray-600 dark:text-gray-400">
            Selamat datang! Berikut adalah ringkasan aktivitas perpustakaan Anda.
        </p>
    </header>

    {{--
      Grid Statistik
      Menjadi 5 kolom dan ditambahkan wire:poll
    --}}
    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-5" wire:poll.15s>

        {{-- Widget 1: Total Buku --}}
        <div class="p-6 bg-white rounded-lg shadow-md dark:bg-gray-800">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 uppercase dark:text-gray-400">Total Judul Buku</p>
                    <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ $totalBooks }}</p>
                </div>
                <span class="p-3 bg-blue-100 rounded-full dark:bg-blue-900">
                    <svg class="w-6 h-6 text-blue-600 dark:text-blue-300" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.185 0 4.236.638 6 1.756 1.764-1.118 3.815-1.756 6-1.756 2.185 0 4.236.638 6 1.756V4.262c-.938-.332-1.948-.512-3-.512-2.185 0-4.236.638-6 1.756z" />
                    </svg>
                </span>
            </div>
        </div>

        {{-- Widget 2: Total Anggota --}}
        <div class="p-6 bg-white rounded-lg shadow-md dark:bg-gray-800">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 uppercase dark:text-gray-400">Total Anggota</p>
                    <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ $totalMembers }}</p>
                </div>
                <span class="p-3 bg-green-100 rounded-full dark:bg-green-900">
                    <svg class="w-6 h-6 text-green-600 dark:text-green-300" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0z" />
                    </svg>
                </span>
            </div>
        </div>

        {{-- Widget 3: Buku Dipinjam --}}
        <div class="p-6 bg-white rounded-lg shadow-md dark:bg-gray-800">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 uppercase dark:text-gray-400">Buku Dipinjam</p>
                    <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ $totalOnLoan }}</p>
                </div>
                <span class="p-3 bg-yellow-100 rounded-full dark:bg-yellow-900">
                    <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-300" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" />
                    </svg>
                </span>
            </div>
        </div>

        {{-- Widget 4: Total Kategori --}}
        <div class="p-6 bg-white rounded-lg shadow-md dark:bg-gray-800">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 uppercase dark:text-gray-400">Total Kategori</p>
                    <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ $totalCategories }}</p>
                </div>
                <span class="p-3 bg-purple-100 rounded-full dark:bg-purple-900">
                    <svg class="w-6 h-6 text-purple-600 dark:text-purple-300" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.703.542.923-.33 1.54-1.26.91-2.124l-9.58-9.581a2.25 2.25 0 010-3.182z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25v.008" />
                    </svg>
                </span>
            </div>
        </div>

        {{-- Widget 5: Total Ulasan (BARU) --}}
        <div class="p-6 bg-white rounded-lg shadow-md dark:bg-gray-800">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 uppercase dark:text-gray-400">Total Ulasan</p>
                    <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ $totalReviews }}</p>
                </div>
                <span class="p-3 bg-pink-100 rounded-full dark:bg-pink-900">
                    {{-- Icon baru untuk ulasan --}}
                    <svg class="w-6 h-6 text-pink-600 dark:text-pink-300" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H8.25m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H12m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0h-.375M21 12c0 4.556-3.86 8.25-8.625 8.25a8.62 8.62 0 01-8.625-8.25C3.75 7.444 7.64 3.75 12.375 3.75c4.766 0 8.625 3.694 8.625 8.25z" />
                    </svg>
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
