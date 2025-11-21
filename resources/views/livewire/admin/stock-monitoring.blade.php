<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use App\Models\Book;


new class extends Component
{
    public function with(): array
    {
        return [
            // Logic: Mengambil buku yang Total Lisensinya (Aset) kurang dari 5
            'books' => Book::where('lisensi', '<', 5)
                        ->orderBy('lisensi', 'asc') // Prioritaskan yang 0
                        ->get()
        ];
    }
}; ?>

<div>
    <main class="flex-1 p-6 lg:p-10">
        {{-- HEADER --}}
        <header class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                Pemantauan Stok Buku
            </h1>
            <p class="mt-1 text-gray-600 dark:text-gray-400">
                Daftar buku yang total lisensinya menipis (kurang dari 5) dan perlu pengadaan ulang.
            </p>
        </header>

        {{-- KARTU STATISTIK --}}
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4 mb-8">
            <div class="p-6 bg-white rounded-lg shadow-md dark:bg-gray-800">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500 uppercase dark:text-gray-400">Perlu Restock</p>
                        <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ $books->count() }}</p>
                    </div>
                    <span class="p-3 bg-yellow-100 rounded-full dark:bg-yellow-900">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-yellow-600 dark:text-yellow-400"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3"/><path d="M12 9v4"/><path d="M12 17h.01"/></svg>
                    </span>
                </div>
            </div>
        </div>

        {{-- TABEL --}}
        <div class="p-6 bg-white rounded-lg shadow-md dark:bg-gray-800">
            <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">Daftar Buku Kritis</h3>

            <div class="overflow-x-auto">
                <table class="w-full min-w-full text-left align-middle">
                    <thead class="border-b border-gray-200 dark:border-gray-700">
                        <tr class="text-xs font-medium text-gray-500 uppercase dark:text-gray-400">
                            <th class="px-4 py-3">Judul Buku</th>
                            <th class="px-4 py-3">Penulis</th>
                            <th class="px-4 py-3 text-center">Total Lisensi</th>
                            <th class="px-4 py-3 text-center">Sedang Dipinjam</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse ($books as $book)
                            <tr wire:key="{{ $book->id }}" class="text-sm text-gray-900 dark:text-white hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                                {{-- PERBAIKAN: Menggunakan 'judul' bukan 'title' --}}
                                <td class="px-4 py-3 font-medium">{{ $book->judul }}</td>

                                {{-- PERBAIKAN: Menggunakan 'penulis' bukan 'author' --}}
                                <td class="px-4 py-3 text-gray-700 dark:text-gray-300">{{ $book->penulis ?? '-' }}</td>

                                <td class="px-4 py-3 text-center">
                                    @if ($book->lisensi == 0)
                                        <span class="px-2 py-1 text-xs font-bold rounded-full bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                                            KOSONG (0)
                                        </span>
                                    @else
                                        <span class="px-2 py-1 text-xs font-medium rounded-full bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">
                                            {{ $book->lisensi }}
                                        </span>
                                    @endif
                                </td>

                                {{-- Tambahan Info: Menampilkan jumlah yang sedang dipinjam agar Admin tau sisa real --}}
                                <td class="px-4 py-3 text-center text-gray-500">
                                    {{ $book->jumlah_dipinjam }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-8 text-center">
                                    <div class="flex flex-col items-center justify-center">
                                        <svg class="h-12 w-12 text-green-400 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        <h3 class="text-lg font-medium text-green-700 dark:text-green-400">Stok Aman!</h3>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">Semua buku memiliki lisensi lebih dari 5.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>
