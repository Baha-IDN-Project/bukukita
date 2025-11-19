<?php

use App\Models\Review;
use Livewire\Volt\Component;
use Livewire\WithPagination;

// Mendefinisikan komponen Volt
new class extends Component
{
    use WithPagination;

    /**
     * Menyediakan data ulasan yang sudah dipaginasi
     * dan di-eager load ke view.
     */
    public function with(): array
    {
        return [
            'reviews' => Review::with(['user', 'book']) // Eager load relasi
                                ->latest() // Tampilkan yang terbaru dulu
                                ->paginate(10), // Paginasi per 10 data
        ];
    }

    /**
     * Menghapus ulasan berdasarkan ID.
     * Livewire akan otomatis meng-hydrate model Review.
     */
    public function delete(Review $review): void
    {
        $review->delete();

        // Kirim pesan sukses ke sesi
        session()->flash('success', 'Review successfully deleted.');
    }
}; ?>

{{-- Menggunakan layout utama (asumsi Anda menggunakan layout app) --}}


   <div>
    <main class="flex-1 p-6 lg:p-10">
        {{-- HEADER --}}
        <header class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                Manajemen Ulasan (Reviews)
            </h1>
            <p class="mt-1 text-gray-600 dark:text-gray-400">
                Daftar semua ulasan yang masuk dari pengguna.
            </p>
        </header>

        {{-- KARTU STATISTIK (Diadaptasi untuk Ulasan) --}}
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4 mb-8">
            <div class="p-6 bg-white rounded-lg shadow-md dark:bg-gray-800">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500 uppercase dark:text-gray-400">Total Ulasan</p>
                        {{-- Menggunakan total() dari paginator --}}
                        <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ $reviews->total() }}</p>
                    </div>
                    {{-- Ikon diganti menjadi ikon ulasan/komentar --}}
                    <span class="p-3 bg-blue-100 rounded-full dark:bg-blue-900">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-message-square-more-icon lucide-message-square-more"><path d="M22 17a2 2 0 0 1-2 2H6.828a2 2 0 0 0-1.414.586l-2.202 2.202A.71.71 0 0 1 2 21.286V5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2z"/><path d="M12 11h.01"/><path d="M16 11h.01"/><path d="M8 11h.01"/></svg>
                    </span>
                </div>
            </div>
            {{-- Anda bisa menambahkan kartu statistik lain di sini --}}
        </div>

        {{-- AREA KONTEN UTAMA (TABEL ULASAN) --}}
        <div class="p-6 bg-white rounded-lg shadow-md dark:bg-gray-800">

            @if (session('success'))
                <div class="mb-4 p-4 bg-green-100 text-green-700 border border-green-300 rounded-lg dark:bg-green-900 dark:text-green-200 dark:border-green-700">
                    {{ session('success') }}
                </div>
            @endif

            <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">Daftar Ulasan Pengguna</h3>

            <div class="overflow-x-auto">
                <table class="w-full min-w-full text-left align-middle">
                    <thead class="border-b border-gray-200 dark:border-gray-700">
                        <tr class="text-xs font-medium text-gray-500 uppercase dark:text-gray-400">
                            <th class="px-4 py-3">Pengguna</th>
                            <th class="px-4 py-3">Buku</th>
                            <th class="px-4 py-3">Rating</th>
                            <th class="px-4 py-3">Ulasan</th>
                            <th class="px-4 py-3">Tanggal</th>
                            <th class="px-4 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">

                        @forelse ($reviews as $review)
                            <tr wire:key="{{ $review->id }}" class="text-sm text-gray-900 dark:text-white">
                                <td class="px-4 py-3 font-medium">
                                    {{ $review->user->name ?? 'User Dihapus' }}
                                </td>
                                <td class="px-4 py-3 text-gray-700 dark:text-gray-300">
                                    {{ $review->book->title ?? 'Buku Dihapus' }}
                                </td>
                                <td class="px-4 py-3 text-gray-700 dark:text-gray-300">
                                    {{-- Menampilkan rating sebagai bintang (opsional, tapi lebih bagus) --}}
                                    <span class="flex items-center">
                                        @for ($i = 1; $i <= 5; $i++)
                                            <svg class="w-4 h-4 {{ $i <= $review->rating ? 'text-yellow-400' : 'text-gray-300 dark:text-gray-600' }}" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.83 5.626a1 1 0 00.95.69h5.91c.969 0 1.371 1.24.588 1.81l-4.78 3.47a1 1 0 00-.364 1.118l1.83 5.626c.3.921-.755 1.688-1.54 1.118l-4.78-3.47a1 1 0 00-1.176 0l-4.78 3.47c-.784.57-1.838-.197-1.54-1.118l1.83-5.626a1 1 0 00-.364-1.118L2.02 11.053c-.783-.57-.38-1.81.588-1.81h5.91a1 1 0 00.95-.69L9.049 2.927z" />
                                            </svg>
                                        @endfor
                                        <span class="ml-1.5 text-gray-700 dark:text-gray-300">({{ $review->rating }})</span>
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-gray-700 dark:text-gray-300">
                                    {{ Str::limit($review->ulasan, 80) }}
                                </td>
                                <td class="px-4 py-3 text-gray-700 dark:text-gray-300">
                                    {{ $review->created_at->format('d M Y') }}
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <button
                                        wire:click="delete({{ $review->id }})"
                                        wire:confirm="Anda yakin ingin menghapus ulasan ini?"
                                        class="text-sm font-medium text-red-600 hover:text-red-900 dark:text-red-500 dark:hover:text-red-400">
                                        Hapus
                                    </button>
                                </td>
                            </tr>
                        @empty
                            {{-- Tampilan jika tidak ada ulasan --}}
                            <tr>
                                <td colspan="6" class="px-4 py-4 text-center text-sm text-gray-500 dark:text-gray-400">
                                    <div class="py-8">
                                        <svg class="mx-auto h-12 w-12 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H8.25m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H12m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0h-.375M21 12c0 4.556-3.86 8.25-8.625 8.25a8.62 8.62 0 01-8.625-8.25C3.75 7.444 7.64 3.75 12.375 3.75c4.766 0 8.625 3.694 8.625 8.25z" />
                                        </svg>
                                        <h3 class="mt-2 text-lg font-medium text-gray-700 dark:text-gray-200">Belum Ada Ulasan</h3>
                                        <p class="mt-1 text-sm">Belum ada ulasan yang ditinggalkan oleh pengguna.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse

                    </tbody>
                </table>
            </div>

            {{-- Link Paginasi --}}
            <div class="mt-6">
                {{ $reviews->links() }}
            </div>
        </div>

    </main>
</div>
