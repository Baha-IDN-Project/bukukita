<?php

use App\Models\Review;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Url;

new class extends Component
{
    use WithPagination;

    #[Url]
    public $search = '';

    #[Url]
    public $ratingFilter = '';

    /**
     * Menyediakan data ulasan yang sudah dipaginasi
     */
    public function with(): array
    {
        $query = Review::with(['user', 'book'])->latest();

        // Fitur Pencarian (Search by User Name, Book Title, or Review content)
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('ulasan', 'like', '%' . $this->search . '%')
                  ->orWhereHas('user', fn($u) => $u->where('name', 'like', '%' . $this->search . '%'))
                  ->orWhereHas('book', fn($b) => $b->where('title', 'like', '%' . $this->search . '%'));
            });
        }

        // Fitur Filter Rating
        if ($this->ratingFilter) {
            $query->where('rating', $this->ratingFilter);
        }

        // Data Statistik Sederhana
        $stats = [
            'total' => Review::count(),
            'avg' => Review::avg('rating'),
            'five_star' => Review::where('rating', 5)->count(),
        ];

        return [
            'reviews' => $query->paginate(10),
            'stats' => $stats,
        ];
    }

    /**
     * Reset pagination saat melakukan pencarian
     */
    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedRatingFilter()
    {
        $this->resetPage();
    }

    public function delete(Review $review): void
    {
        $review->delete();
        session()->flash('success', 'Review successfully deleted.');
        // Refresh statistik bisa ditambahkan jika perlu, tapi livewire akan merender ulang
    }
}; ?>

<div>
    <main class="flex-1 p-6 lg:p-10 bg-gray-50 dark:bg-gray-900 min-h-screen">

        {{-- HEADER & ACTIONS --}}
        <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8 gap-4">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white tracking-tight">
                    Manajemen Ulasan
                </h1>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    Pantau dan kelola feedback dari pembaca.
                </p>
            </div>

            {{-- Tombol Aksi (Opsional, misal Export) --}}
            {{-- <button class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">Export Data</button> --}}
        </div>

        {{-- STATISTIK CARDS --}}
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 mb-8">
            <!-- Card 1: Total -->
            <div class="p-6 bg-white border border-gray-100 rounded-xl shadow-sm hover:shadow-md transition-shadow dark:bg-gray-800 dark:border-gray-700">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500 uppercase dark:text-gray-400">Total Ulasan</p>
                        <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2">{{ $stats['total'] }}</p>
                    </div>
                    <div class="p-3 bg-blue-50 text-blue-600 rounded-lg dark:bg-blue-900/30 dark:text-blue-400">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z"></path></svg>
                    </div>
                </div>
            </div>

            <!-- Card 2: Rata-rata -->
            <div class="p-6 bg-white border border-gray-100 rounded-xl shadow-sm hover:shadow-md transition-shadow dark:bg-gray-800 dark:border-gray-700">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500 uppercase dark:text-gray-400">Rata-rata Rating</p>
                        <div class="flex items-center mt-2">
                            <p class="text-3xl font-bold text-gray-900 dark:text-white mr-2">{{ number_format($stats['avg'], 1) }}</p>
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5 text-yellow-500">
                                    <path fill-rule="evenodd" d="M10.788 3.21c.448-1.077 1.976-1.077 2.424 0l2.082 5.007 5.404.433c1.164.093 1.636 1.545.749 2.305l-4.117 3.527 1.257 5.273c.271 1.136-.964 2.033-1.96 1.425L12 18.354 7.373 21.18c-.996.608-2.231-.29-1.96-1.425l1.257-5.273-4.117-3.527c-.887-.76-.415-2.212.749-2.305l5.404-.433 2.082-5.006z" clip-rule="evenodd" />
                            </svg>
                        </div>
                    </div>
                    <div class="p-3 bg-yellow-50 text-yellow-600 rounded-lg dark:bg-yellow-900/30 dark:text-yellow-400">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                    </div>
                </div>
            </div>

            <!-- Card 3: Bintang 5 -->
            <div class="p-6 bg-white border border-gray-100 rounded-xl shadow-sm hover:shadow-md transition-shadow dark:bg-gray-800 dark:border-gray-700">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500 uppercase dark:text-gray-400">Ulasan Bintang 5</p>
                        <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2">{{ $stats['five_star'] }}</p>
                    </div>
                    <div class="p-3 bg-green-50 text-green-600 rounded-lg dark:bg-green-900/30 dark:text-green-400">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                </div>
            </div>
        </div>

        {{-- FILTER & SEARCH BAR --}}
        <div class="flex flex-col sm:flex-row justify-between items-center gap-4 mb-6">
            <div class="relative w-full sm:w-72">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="Cari user, buku, atau isi..."
                    class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 sm:text-sm dark:bg-gray-800 dark:border-gray-700 dark:text-white transition duration-150 ease-in-out">
            </div>

            <div class="flex items-center gap-2 w-full sm:w-auto">
                <select wire:model.live="ratingFilter" class="block w-full sm:w-48 pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-lg dark:bg-gray-800 dark:border-gray-700 dark:text-white">
                    <option value="">Semua Rating</option>
                    <option value="5">⭐⭐⭐⭐⭐ (5 Bintang)</option>
                    <option value="4">⭐⭐⭐⭐ (4 Bintang)</option>
                    <option value="3">⭐⭐⭐ (3 Bintang)</option>
                    <option value="2">⭐⭐ (2 Bintang)</option>
                    <option value="1">⭐ (1 Bintang)</option>
                </select>
            </div>
        </div>

        {{-- FLASH MESSAGE --}}
        @if (session('success'))
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)"
                class="mb-6 flex items-center p-4 text-sm text-green-800 bg-green-50 border border-green-200 rounded-lg dark:bg-green-900/50 dark:text-green-300 dark:border-green-800" role="alert">
                <svg class="flex-shrink-0 inline w-4 h-4 mr-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z"/>
                </svg>
                <span class="font-medium">Berhasil!</span> &nbsp; {{ session('success') }}
            </div>
        @endif

        {{-- TABLE CONTENT --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden dark:bg-gray-800 dark:border-gray-700">
            <!-- Loading Indicator Overlay -->
            <div wire:loading.flex wire:target="search, ratingFilter, delete, page" class="absolute inset-0 bg-white/50 dark:bg-gray-900/50 z-10 items-center justify-center hidden">
                <svg class="animate-spin h-8 w-8 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-gray-50 dark:bg-gray-700/50">
                        <tr>
                            <th scope="col" class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider dark:text-gray-400">Pengguna</th>
                            <th scope="col" class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider dark:text-gray-400">Buku</th>
                            <th scope="col" class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider dark:text-gray-400">Rating & Ulasan</th>
                            <th scope="col" class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider dark:text-gray-400">Tanggal</th>
                            <th scope="col" class="px-6 py-4 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider dark:text-gray-400">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse ($reviews as $review)
                            <tr wire:key="{{ $review->id }}" class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors duration-150">
                                {{-- User Column --}}
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10">
                                            {{-- Avatar Placeholder with Initials --}}
                                            <div class="h-10 w-10 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 font-bold text-sm dark:bg-indigo-900 dark:text-indigo-300">
                                                {{ substr($review->user->name ?? '?', 0, 2) }}
                                            </div>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900 dark:text-white">
                                                {{ $review->user->name ?? 'User Dihapus' }}
                                            </div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                                {{ $review->user->email ?? '' }}
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                {{-- Book Column --}}
                                <td class="px-6 py-4">
                                    <div class="flex items-center">
                                        <div class="p-2 bg-gray-100 rounded dark:bg-gray-700 mr-3 text-gray-500">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
                                        </div>
                                        <span class="text-sm font-medium text-gray-900 dark:text-gray-200 line-clamp-1 max-w-[150px]" title="{{ $review->book->title ?? '' }}">
                                            {{ $review->book->title ?? 'Buku Dihapus' }}
                                        </span>
                                    </div>
                                </td>

                                {{-- Rating & Review Column --}}
                                <td class="px-6 py-4 max-w-xs">
                                    <div class="flex items-center mb-1">
                                        <div class="flex text-yellow-400 mr-2">
                                            @for ($i = 1; $i <= 5; $i++)
                                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5 text-yellow-500">
                                                            <path fill-rule="evenodd" d="M10.788 3.21c.448-1.077 1.976-1.077 2.424 0l2.082 5.007 5.404.433c1.164.093 1.636 1.545.749 2.305l-4.117 3.527 1.257 5.273c.271 1.136-.964 2.033-1.96 1.425L12 18.354 7.373 21.18c-.996.608-2.231-.29-1.96-1.425l1.257-5.273-4.117-3.527c-.887-.76-.415-2.212.749-2.305l5.404-.433 2.082-5.006z" clip-rule="evenodd" />
                                                    </svg>
                                            @endfor
                                        </div>
                                        <span class="px-2 py-0.5 rounded text-xs font-semibold bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300">
                                            {{ $review->rating }}.0
                                        </span>
                                    </div>
                                    <p class="text-sm text-gray-600 dark:text-gray-400 line-clamp-2">
                                        "{{ Str::limit($review->ulasan, 100) }}"
                                    </p>
                                </td>

                                {{-- Date Column --}}
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    {{ $review->created_at->format('d M Y') }}
                                    <div class="text-xs text-gray-400 mt-0.5">
                                        {{ $review->created_at->format('H:i') }}
                                    </div>
                                </td>

                                {{-- Actions Column --}}
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <button
                                        wire:click="delete({{ $review->id }})"
                                        wire:confirm="Yakin ingin menghapus ulasan ini? Tindakan ini tidak dapat dibatalkan."
                                        class="text-gray-400 hover:text-red-600 transition-colors p-2 rounded-full hover:bg-red-50 dark:hover:bg-red-900/20"
                                        title="Hapus Ulasan"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center justify-center">
                                        <div class="bg-gray-100 p-4 rounded-full dark:bg-gray-700 mb-4">
                                            <svg class="h-8 w-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                        </div>
                                        <h3 class="text-lg font-medium text-gray-900 dark:text-white">Tidak ada ulasan ditemukan</h3>
                                        <p class="text-gray-500 dark:text-gray-400 mt-1 max-w-sm">
                                            Coba ubah kata kunci pencarian atau filter rating Anda.
                                        </p>
                                        @if($search || $ratingFilter)
                                            <button wire:click="$set('search', ''); $set('ratingFilter', '')" class="mt-4 text-indigo-600 hover:text-indigo-500 font-medium text-sm">
                                                Reset Filter
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination Footer --}}
            <div class="bg-gray-50 px-6 py-4 border-t border-gray-200 dark:bg-gray-800 dark:border-gray-700">
                {{ $reviews->links() }}
            </div>
        </div>
    </main>
</div>
