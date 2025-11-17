<?php
use App\Models\User;
use App\Models\Book;
use App\Models\Peminjaman; // Diubah dari Loan
use App\Models\Category; // Asumsi Anda memiliki model Category
use function Livewire\Volt\layout;
use function Livewire\Volt\with;

// Mengatur layout utama
layout('components.layouts.app');

// Menggunakan 'with' untuk mengambil data. Ini akan dievaluasi ulang
// setiap kali komponen di-refresh (termasuk oleh wire:poll)
with(function () {
    return [
        'totalMembers' => User::count(),
        'totalBooks' => Book::count(),
        'totalCategories' => Category::count(), // Statistik tambahan seperti yang Anda minta
        'totalOnLoan' => Peminjaman::whereNull('tanggal_harus_kembali')->count(), // Diubah dari Loan

        // Data untuk tabel aktivitas terkini
        'recentPeminjaman' => Peminjaman::with(['user', 'book']) // Diubah dari Loan dan recentLoans
                            ->latest() // Urutkan berdasarkan terbaru
                            ->take(5) // Ambil 5 data terakhir
                            ->get()
    ];
});

// Aksi untuk tombol "Tindakan Cepat"
$bukaModalTambahBuku = fn() => $this->dispatch('open-modal', 'tambah-buku');
$bukaModalTambahAnggota = fn() => $this->dispatch('open-modal', 'tambah-anggota');

?>

    {{--
      Konten Halaman
      Kita tambahkan `wire:poll.15s` di sini untuk me-refresh seluruh
      komponen (termasuk semua statistik) setiap 15 detik.
    --}}
<main class="flex-1 p-6 lg:p-10" wire:poll.15s>
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
      Saya mengubahnya menjadi 5 kolom untuk mengakomodasi "Total Kategori"
    --}}
    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-5">

        {{-- Widget 1: Total Buku --}}
        <div class="p-6 bg-white rounded-lg shadow-md dark:bg-gray-800">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 uppercase dark:text-gray-400">Total Judul Buku</p>
                    {{-- Data dinamis dari 'with()' --}}
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
                    {{-- Data dinamis dari 'with()' --}}
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
                    {{-- Data dinamis dari 'with()' --}}
                    <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ $totalOnLoan }}</p>
                </div>
                <span class="p-3 bg-yellow-100 rounded-full dark:bg-yellow-900">
                    <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-300" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" />
                    </svg>
                </span>
            </div>
        </div>

        {{-- Widget 4: Total Kategori (Sesuai permintaan) --}}
        <div class="p-6 bg-white rounded-lg shadow-md dark:bg-gray-800">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 uppercase dark:text-gray-400">Total Kategori</p>
                    {{-- Data dinamis dari 'with()' --}}
                    <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ $totalCategories }}</p>
                </div>
                <span class="p-3 bg-purple-100 rounded-full dark:bg-purple-900">
                    {{-- Icon baru untuk kategori (tags) --}}
                    <svg class="w-6 h-6 text-purple-600 dark:text-purple-300" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.703.542.923-.33 1.54-1.26.91-2.124l-9.58-9.581a2.25 2.25 0 010-3.182z" />
                      <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25v.008" />
                    </svg>
                </span>
            </div>
        </div>

        {{-- Widget 5: Lewat Batas (dari template, datanya dinamis) --}}

    </div>

    <div class="grid grid-cols-1 gap-6 mt-8 lg:grid-cols-3">

        {{-- Panel Tindakan Cepat (Aksi sudah di-wire) --}}
        <div class="lg:col-span-1">
            <div class="p-6 bg-white rounded-lg shadow-md dark:bg-gray-800">
                <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">Tindakan Cepat</h3>
                <div class="flex flex-col space-y-3">
                    {{-- Tombol ini sekarang memanggil aksi Volt --}}
                    <button wire:click="bukaModalTambahBuku" class="w-full px-4 py-2 font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800">
                        Tambah Buku Baru
                    </button>
                    <button wire:click="bukaModalTambahAnggota" class="w-full px-4 py-2 font-medium text-white bg-green-600 rounded-lg hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800">
                        Registrasi Anggota
                    </button>
                    {{-- Anda bisa mengubah ini menjadi wire:click atau biarkan sebagai link --}}
                    <a href="#" class="w-full px-4 py-2 font-medium text-center text-gray-900 bg-gray-100 rounded-lg dark:bg-gray-700 dark:text-white hover:bg-gray-200 dark:hover:bg-gray-600">
                        Proses Peminjaman
                    </a>
                    <a href="#" class="w-full px-4 py-2 font-medium text-center text-gray-900 bg-gray-100 rounded-lg dark:bg-gray-700 dark:text-white hover:bg-gray-200 dark:hover:bg-gray-600">
                        Proses Pengembalian
                    </a>
                </div>
            </div>
        </div>

        {{-- Panel Aktivitas Terkini (Dibuat Dinamis) --}}
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
                            {{-- Loop dinamis menggunakan data dari 'with()' --}}
                            @forelse ($recentPeminjaman as $peminjaman) {{-- Diubah dari $recentLoans as $loan --}}
                                <tr classs="text-gray-900 dark:text-white">
                                    <td class="py-3">{{ $peminjaman->user->name ?? 'N/A' }}</td> {{-- Diubah dari $loan --}}
                                    <td class="py-3">{{ $peminjaman->book->title ?? 'N/A' }}</td> {{-- Diubah dari $loan --}}
                                    <td class="py-3">
                                        {{-- Logika untuk status badge --}}
                                        @if ($peminjaman->tanggal_harus_kembali) {{-- Diubah dari $loan --}}
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
                                        {{-- Tampilkan waktu yang mudah dibaca --}}
                                        {{ $peminjaman->created_at->diffForHumans() }} {{-- Diubah dari $loan --}}
                                    </td>
                                </tr>
                            @empty
                                {{-- Kondisi jika tidak ada data --}}
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
