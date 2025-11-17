<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use App\Models\Book; // Pastikan model Book Anda ada di sini
use Illuminate\Database\Eloquent\Collection;

new class extends Component
{
    /**
     * Mengambil data buku yang stoknya menipis.
     * Metode 'with' ini akan otomatis mengirimkan variabel $books ke view.
     */
    public function with(): array
    {
        return [
            'books' => Book::where('lisensi', '<', 5) // Sesuai permintaan: stok < 5 (mencakup 0)
                            ->orderBy('lisensi', 'asc') // Tampilkan yang stoknya 0 di paling atas
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
                Daftar buku yang stoknya menipis (kurang dari 5) atau telah habis.
            </p>
        </header>

        {{-- KARTU STATISTIK --}}
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4 mb-8">
            <div class="p-6 bg-white rounded-lg shadow-md dark:bg-gray-800">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500 uppercase dark:text-gray-400">Buku Stok Menipis</p>
                        {{-- Menggunakan count() pada collection $books --}}
                        <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ $books->count() }}</p>
                    </div>
                    {{-- Ikon diganti menjadi ikon peringatan --}}
                    <span class="p-3 bg-yellow-100 rounded-full dark:bg-yellow-900">
                        <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-300" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126z" />
                        </svg>
                    </span>
                </div>
            </div>
            {{-- Anda bisa menambahkan kartu statistik lain di sini jika diperlukan --}}
        </div>

        {{-- AREA KONTEN UTAMA (TABEL STOK) --}}
        {{-- Dibungkus dengan card seperti di contoh kedua --}}
        <div class="p-6 bg-white rounded-lg shadow-md dark:bg-gray-800">
            <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">Daftar Buku Stok Menipis</h3>

            <div class="overflow-x-auto">
                {{-- Menggunakan style tabel dari contoh kedua --}}
                <table class="w-full min-w-full text-left align-middle">
                    <thead class="border-b border-gray-200 dark:border-gray-700">
                        <tr class="text-xs font-medium text-gray-500 uppercase dark:text-gray-400">
                            <th class="px-4 py-3">Judul Buku</th>
                            <th class="px-4 py-3">Penulis</th>
                            <th class="px-4 py-3">Stok Saat Ini</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">

                        {{-- Loop data buku menggunakan @forelse (dari file asli) --}}
                        @forelse ($books as $book)
                            <tr wire:key="{{ $book->id }}" class="text-sm text-gray-900 dark:text-white">
                                <td class="px-4 py-3 font-medium">{{ $book->title }}</td>
                                <td class="px-4 py-3 text-gray-700 dark:text-gray-300">{{ $book->author ?? '-' }}</td>
                                <td class="px-4 py-3">

                                    {{-- Menggunakan style badge yang lebih kecil (seperti di contoh 2) --}}
                                    @if ($book->lisensi == 0)
                                        <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                                            HABIS (0)
                                        </span>
                                    @else
                                        <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">
                                            {{ $book->lisensi }}
                                        </span>
                                    @endif

                                </td>
                            </tr>
                        @empty
                            {{-- Tampilan jika tidak ada buku --}}
                            <tr>
                                <td colspan="3" class="px-4 py-4 text-center text-sm text-gray-500 dark:text-gray-400">
                                    {{-- Menggunakan style @empty dari file asli, sudah bagus --}}
                                    <div class="py-8">
                                        <svg class="mx-auto h-12 w-12 text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        <h3 class="mt-2 text-lg font-medium text-green-700">Stok Aman!</h3>
                                        <p class="mt-1 text-sm">Tidak ada buku yang stoknya menipis saat ini.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse

                    </tbody>
                </table>
            </div>

            {{-- Bagian Paginasi tidak diperlukan karena file asli menggunakan ->get() bukan ->paginate() --}}
        </div>

    </main>
</div>
