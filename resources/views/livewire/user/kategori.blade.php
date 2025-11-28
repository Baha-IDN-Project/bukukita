<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use App\Models\Category;

new #[Layout('components.layouts.user')]
class extends Component {

    // Menyimpan query pencarian di URL agar bisa di-share/refresh
    #[Url]
    public string $search = '';

    /**
     * Mengambil data kategori beserta jumlah bukunya.
     */
    #[Computed]
    public function categories()
    {
        return Category::query()
            ->withCount('books') // Menghitung jumlah buku di setiap kategori
            ->when($this->search, function ($query) {
                $query->where('nama_kategori', 'like', '%' . $this->search . '%');
            })
            ->orderBy('nama_kategori', 'asc')
            ->get();
    }

    /**
     * Helper warna background dinamis (sama seperti di dashboard)
     */
    public function getCategoryColor($index)
    {
        $colors = ['e67e22', '2980b9', 'c0392b', '8e44ad', '16a085', 'f1c40f', '2c3e50', 'd35400'];
        return $colors[$index % count($colors)];
    }
}; ?>

<div class="min-h-screen bg-gray-950 text-gray-100">

    {{-- Header Section --}}
    <div class="relative overflow-hidden bg-gray-900 shadow-xl border-b border-gray-800">
        <div class="absolute inset-0 bg-gradient-to-r from-indigo-900/20 to-purple-900/20"></div>
        <div class="relative mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
            <h1 class="text-3xl font-extrabold tracking-tight text-white sm:text-4xl">
                Jelajahi Kategori
            </h1>
            <p class="mt-2 max-w-2xl text-lg text-gray-400">
                Temukan buku berdasarkan topik yang kamu minati dari koleksi kami.
            </p>
        </div>
    </div>

    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">

        {{-- Sticky Search Bar --}}
        <div class="sticky top-4 z-30 mb-8 rounded-xl border border-gray-700/50 bg-gray-900/80 p-4 shadow-lg backdrop-blur-md transition-all">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">

                {{-- Search Input --}}
                <div class="relative w-full">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                        <svg class="h-5 w-5 text-gray-500" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <input wire:model.live.debounce.300ms="search"
                           type="text"
                           class="block w-full rounded-lg border border-gray-700 bg-gray-800 py-2.5 pl-10 pr-3 text-sm text-gray-200 placeholder-gray-500 focus:border-indigo-500 focus:bg-gray-900 focus:ring-1 focus:ring-indigo-500"
                           placeholder="Cari kategori...">
                </div>
            </div>

            {{-- Loading Indicator Overlay (Specific to Search) --}}
            <div wire:loading.flex class="absolute inset-0 z-10 items-center justify-center rounded-xl bg-gray-900/60 backdrop-blur-[2px]">
                <div class="flex items-center gap-2 rounded-full bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-lg">
                   <svg class="h-4 w-4 animate-spin text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Memuat...
                </div>
            </div>
        </div>

        {{-- Category Grid --}}
        <div class="min-h-[500px]">
            <div class="grid grid-cols-2 gap-6 sm:grid-cols-3 md:grid-cols-4 xl:grid-cols-5">
                @forelse ($this->categories as $index => $category)
                    <a href="{{ route('user.koleksi', ['selectedCategory' => $category->id]) }}"
                       wire:navigate
                       class="group relative flex flex-col overflow-hidden rounded-2xl bg-gray-900 shadow-md ring-1 ring-white/5 transition-all duration-300 hover:-translate-y-2 hover:shadow-2xl hover:shadow-indigo-500/10">

                        {{-- Image Section (Using Aspect Video logic from target, but styled like reference) --}}
                        <div class="relative aspect-video w-full overflow-hidden">
                            <img src="https://placehold.co/400x300/{{ $this->getCategoryColor($index) }}/ffffff?text={{ urlencode($category->nama_kategori) }}"
                                 alt="{{ $category->nama_kategori }}"
                                 loading="lazy"
                                 class="h-full w-full object-cover transition-transform duration-700 group-hover:scale-110">

                            {{-- Dark Gradient Overlay --}}
                            <div class="absolute inset-0 bg-gradient-to-t from-gray-950 via-gray-950/20 to-transparent opacity-60 transition-opacity group-hover:opacity-80"></div>

                            {{-- Hover Button (Explore) --}}
                            <div class="absolute inset-0 flex items-center justify-center opacity-0 transition-opacity duration-300 group-hover:opacity-100">
                                <span class="translate-y-4 rounded-full bg-indigo-600 px-4 py-1.5 text-xs font-bold text-white shadow-lg transition-all duration-300 group-hover:translate-y-0 hover:bg-indigo-500">
                                    Lihat Koleksi
                                </span>
                            </div>
                        </div>

                        {{-- Card Content --}}
                        <div class="flex flex-1 flex-col justify-between p-4">
                            <div>
                                <h3 class="line-clamp-2 text-base font-bold leading-snug text-gray-100 transition-colors group-hover:text-indigo-400" title="{{ $category->nama_kategori }}">
                                    {{ $category->nama_kategori }}
                                </h3>
                            </div>

                            {{-- Footer / Count --}}
                            <div class="mt-3 flex items-center justify-between border-t border-gray-800 pt-3">
                                <span class="text-xs text-gray-500 group-hover:text-gray-400 transition-colors">
                                    Total Buku
                                </span>
                                <span class="inline-flex items-center rounded-md bg-gray-800 px-2 py-1 text-xs font-medium text-indigo-400 ring-1 ring-inset ring-indigo-400/30">
                                    {{ $category->books_count }}
                                </span>
                            </div>
                        </div>
                    </a>
                @empty
                    {{-- Empty State (Styled like Reference) --}}
                    <div class="col-span-full flex flex-col items-center justify-center rounded-2xl border border-dashed border-gray-800 bg-gray-900/50 py-20 text-center">
                        <div class="relative mb-4 flex h-20 w-20 items-center justify-center rounded-full bg-gray-800 shadow-inner">
                            <svg class="h-10 w-10 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                            </svg>
                            <div class="absolute -bottom-1 -right-1 rounded-full bg-gray-900 p-1">
                                <div class="rounded-full bg-indigo-600 p-1.5">
                                    <svg class="h-4 w-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>
                        <h3 class="text-xl font-bold text-white">Kategori tidak ditemukan</h3>
                        <p class="mt-2 max-w-sm text-sm text-gray-400">
                            Coba ubah kata kunci pencarian Anda.
                        </p>
                    </div>
                @endforelse
            </div>
        </div>

    </div>
</div>
