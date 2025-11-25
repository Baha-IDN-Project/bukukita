<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use App\Models\Book;
use App\Models\Category;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

new #[Layout('components.layouts.user')]
class extends Component {

    public Collection $newestBooks;
    public Collection $highestRatedBooks;
    public Collection $categories;

    public function mount(): void
    {
        $this->newestBooks = Book::with('categories')
            ->orderByDesc('created_at')
            ->take(10)
            ->get();

        $this->highestRatedBooks = Book::with('categories')
            ->withAvg('reviews', 'rating')
            ->orderByDesc('reviews_avg_rating')
            ->take(10) // Tampilkan lebih banyak (10) agar scrolling lebih berguna
            ->get();

        $this->categories = Category::withCount('books') // Opsional: hitung jumlah buku
            ->orderByDesc('created_at')
            ->take(8)
            ->get();
    }

    public function getCoverUrl($gambarCover)
    {
        if ($gambarCover) {
            return Storage::url($gambarCover);
        }
        // Placeholder yang lebih netral
        return 'https://placehold.co/400x600/1e293b/cbd5e1?text=No+Cover';
    }

    // Warna gradient untuk kategori agar lebih vivid
    public function getCategoryGradient($index)
    {
        $gradients = [
            'from-orange-400 to-pink-600',
            'from-blue-400 to-indigo-600',
            'from-emerald-400 to-cyan-600',
            'from-purple-400 to-fuchsia-600',
            'from-red-400 to-rose-600',
            'from-teal-400 to-green-600',
        ];
        return $gradients[$index % count($gradients)];
    }
}; ?>

<div class="min-h-screen bg-gray-950 pb-20 text-gray-100">

    {{-- Main Container --}}
    <div class="mx-auto flex w-full max-w-7xl flex-col gap-12 p-4 sm:p-6 lg:p-8">

        {{-- 1. HERO SECTION --}}
        <div class="relative w-full overflow-hidden rounded-3xl bg-gray-900 shadow-2xl ring-1 ring-white/10">
            {{-- Background Image --}}
            <div class="absolute inset-0">
                <img src="https://images.unsplash.com/photo-1481627834876-b7833e8f5570?q=80&w=2228&auto=format&fit=crop"
                     alt="Library Background"
                     class="h-full w-full object-cover opacity-60 transition-transform duration-[20s] hover:scale-110 ease-linear">
                <div class="absolute inset-0 bg-gradient-to-t from-gray-950 via-gray-950/60 to-transparent"></div>
                <div class="absolute inset-0 bg-gradient-to-r from-gray-950/90 via-transparent to-transparent"></div>
            </div>

            {{-- Content --}}
            <div class="relative z-10 flex flex-col justify-center gap-6 px-6 py-16 sm:px-12 sm:py-24 lg:w-2/3">
                <div class="space-y-2">
                    <span class="inline-block rounded-full bg-indigo-500/20 px-3 py-1 text-xs font-medium text-indigo-300 ring-1 ring-inset ring-indigo-500/30">
                        Perpustakaan Digital
                    </span>
                    <h1 class="text-4xl font-extrabold tracking-tight text-white sm:text-5xl lg:text-6xl">
                        Jelajahi Dunia <br> <span class="text-indigo-400">Tanpa Batas</span>
                    </h1>
                </div>
                <p class="max-w-xl text-lg text-gray-300 leading-relaxed">
                    Akses ribuan koleksi buku digital, jurnal, dan referensi terbaik di satu tempat. Mulai petualangan literasimu sekarang.
                </p>

                {{-- Search Bar Aesthetic --}}
                <div class="mt-4 flex w-full max-w-md items-center gap-2 rounded-full bg-white/10 p-1.5 ring-1 ring-white/20 backdrop-blur-md transition-all focus-within:bg-white/20 focus-within:ring-indigo-400">
                    <div class="pl-4 text-gray-400">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="size-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                        </svg>
                    </div>
                    <input type="text" placeholder="Cari judul buku, penulis..." class="w-full border-none bg-transparent py-2 text-sm text-white placeholder-gray-400 focus:ring-0">
                    <button class="rounded-full bg-indigo-600 px-6 py-2 text-sm font-semibold text-white transition hover:bg-indigo-500">
                        Cari
                    </button>
                </div>
            </div>
        </div>

        {{-- 2. BUKU TERBARU (Horizontal Scroll) --}}
        <div>
            <div class="mb-6 flex items-end justify-between px-2">
                <div>
                    <h2 class="text-2xl font-bold text-white">Buku Terbaru</h2>
                    <p class="text-sm text-gray-400">Koleksi yang baru saja ditambahkan minggu ini.</p>
                </div>
                <a href="#" class="group flex items-center gap-1 text-sm font-medium text-indigo-400 transition hover:text-indigo-300">
                    Lihat Semua
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="size-4 transition-transform group-hover:translate-x-1">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                    </svg>
                </a>
            </div>

            <div class="relative group/slider">
                {{-- Scroll Container --}}
                <div class="flex snap-x snap-mandatory gap-6 overflow-x-auto pb-8 pt-2 scrollbar-hide px-2">
                    @forelse ($newestBooks as $book)
                        <div class="snap-start shrink-0">
                            {{-- Card --}}
                            <div class="group relative h-[320px] w-[200px] cursor-pointer overflow-hidden rounded-2xl bg-gray-800 shadow-lg ring-1 ring-white/10 transition-all duration-300 hover:-translate-y-2 hover:shadow-2xl hover:shadow-indigo-500/20">

                                {{-- Image --}}
                                <img src="{{ $this->getCoverUrl($book->gambar_cover) }}"
                                     alt="{{ $book->judul }}"
                                     class="h-full w-full object-cover transition-transform duration-700 group-hover:scale-110">

                                {{-- Gradient Overlay --}}
                                <div class="absolute inset-0 bg-gradient-to-t from-gray-900 via-gray-900/20 to-transparent opacity-80 group-hover:opacity-90"></div>

                                {{-- Badges (Top) --}}
                                <div class="absolute left-3 top-3 flex flex-wrap gap-1">
                                    @if($book->categories->isNotEmpty())
                                        <span class="rounded-md bg-black/50 px-2 py-1 text-[10px] font-bold uppercase tracking-wider text-white backdrop-blur-md">
                                            {{ $book->categories->first()->nama_kategori }}
                                        </span>
                                    @endif
                                </div>

                                {{-- Content (Bottom) --}}
                                <div class="absolute bottom-0 left-0 w-full p-4 transition-all duration-300 group-hover:pb-6">
                                    <h3 class="line-clamp-2 text-lg font-bold leading-tight text-white group-hover:text-indigo-300">
                                        {{ $book->judul }}
                                    </h3>
                                    <p class="mt-1 text-sm text-gray-300">{{ $book->penulis }}</p>

                                    {{-- Hover Action --}}
                                    <div class="mt-3 grid grid-rows-[0fr] transition-all duration-300 group-hover:grid-rows-[1fr]">
                                        <div class="overflow-hidden">
                                            <a href="{{ route('user.buku.detail', $book->slug) }}" wire:navigate
                                               class="block w-full rounded-lg bg-indigo-600 py-2 text-center text-xs font-bold text-white shadow-lg transition hover:bg-indigo-500">
                                                BACA SEKARANG
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="flex w-full flex-col items-center justify-center rounded-2xl border border-dashed border-gray-700 bg-gray-900/50 py-12">
                            <p class="text-gray-500">Belum ada buku terbaru.</p>
                        </div>
                    @endforelse
                </div>
                {{-- Fade effect on right --}}
                <div class="pointer-events-none absolute bottom-0 right-0 top-0 w-24 bg-gradient-to-l from-gray-950 to-transparent"></div>
            </div>
        </div>

        {{-- 3. RATING TERTINGGI (Compact Cards) --}}
        <div>
            <div class="mb-6 flex items-center gap-3 px-2">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-yellow-500/10 text-yellow-500 ring-1 ring-inset ring-yellow-500/20">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-6">
                        <path fill-rule="evenodd" d="M10.788 3.21c.448-1.077 1.976-1.077 2.424 0l2.082 5.007 5.404.433c1.164.093 1.636 1.545.749 2.305l-4.117 3.527 1.257 5.273c.271 1.136-.964 2.033-1.96 1.425L12 18.354 7.373 21.18c-.996.608-2.231-.29-1.96-1.425l1.257-5.273-4.117-3.527c-.887-.76-.415-2.212.749-2.305l5.404-.433 2.082-5.006z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div>
                    <h2 class="text-2xl font-bold text-white">Rating Tertinggi</h2>
                    <p class="text-sm text-gray-400">Paling disukai oleh pembaca kami.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                @forelse ($highestRatedBooks as $book)
                    <a href="{{ route('user.buku.detail', $book->slug) }}" wire:navigate class="group flex gap-4 rounded-xl border border-gray-800 bg-gray-900 p-3 transition-all hover:border-indigo-500/30 hover:bg-gray-800">
                        {{-- Small Cover --}}
                        <div class="relative h-24 w-16 shrink-0 overflow-hidden rounded-lg shadow-md">
                            <img src="{{ $this->getCoverUrl($book->gambar_cover) }}" alt="{{ $book->judul }}" class="h-full w-full object-cover">
                        </div>

                        {{-- Info --}}
                        <div class="flex flex-1 flex-col justify-between py-1">
                            <div>
                                <h3 class="line-clamp-1 font-bold text-white group-hover:text-indigo-400">{{ $book->judul }}</h3>
                                <p class="text-xs text-gray-500">{{ $book->penulis }}</p>
                            </div>

                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-1 rounded-full bg-yellow-500/10 px-2 py-0.5 text-xs font-medium text-yellow-500">
                                    <span>★</span>
                                    <span>{{ number_format($book->reviews_avg_rating, 1) }}</span>
                                </div>
                                <span class="text-[10px] text-gray-600 uppercase tracking-wider group-hover:text-gray-400">Lihat Detail</span>
                            </div>
                        </div>
                    </a>
                @empty
                    <div class="col-span-full py-8 text-center text-gray-500">
                        Belum ada data rating.
                    </div>
                @endforelse
            </div>
        </div>

        {{-- 4. KATEGORI (Modern Grid) --}}
        <div>
            <div class="mb-6 px-2">
                <h2 class="text-2xl font-bold text-white">Jelajahi Kategori</h2>
            </div>

            <div class="grid grid-cols-2 gap-4 md:grid-cols-3 lg:grid-cols-4">
                @forelse ($categories as $index => $category)
                    <a href="#" class="group relative overflow-hidden rounded-2xl p-6 transition-all hover:scale-[1.02] hover:shadow-xl">
                        {{-- Dynamic Gradient Background --}}
                        <div class="absolute inset-0 bg-gradient-to-br {{ $this->getCategoryGradient($index) }} opacity-20 transition-opacity duration-300 group-hover:opacity-30"></div>
                        <div class="absolute inset-0 bg-gray-900/40 backdrop-blur-[1px]"></div>

                        {{-- Border Gradient border --}}
                        <div class="absolute inset-0 rounded-2xl border border-white/5 group-hover:border-white/20"></div>

                        <div class="relative z-10 flex flex-col items-start justify-between gap-4 h-full">
                            {{-- Icon Placeholder (Initial) --}}
                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-white/10 text-white shadow-inner backdrop-blur-sm">
                                <span class="text-lg font-bold">{{ substr($category->nama_kategori, 0, 1) }}</span>
                            </div>

                            <div>
                                <h3 class="text-lg font-bold text-white">{{ $category->nama_kategori }}</h3>
                                <p class="text-xs text-gray-300">{{ $category->books_count ?? 0 }} Buku</p>
                            </div>
                        </div>

                        {{-- Decoration --}}
                        <div class="absolute -bottom-4 -right-4 h-24 w-24 rounded-full bg-gradient-to-br {{ $this->getCategoryGradient($index) }} blur-2xl opacity-40 group-hover:opacity-60 transition-opacity"></div>
                    </a>
                @empty
                    <div class="col-span-full py-12 text-center text-gray-500">
                        Kategori belum tersedia.
                    </div>
                @endforelse
            </div>
        </div>

    </div>
</div>
