<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\WithPagination;
use App\Models\Book;
use App\Models\Category;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Builder;

new #[Layout('components.layouts.user')]
class extends Component {

    use WithPagination;

    // --- STATE ---
    #[Url]
    public $selectedCategory = '';

    #[Url]
    public $selectedAuthor = '';

    #[Url]
    public $sortBy = 'terbaru';

    // Tambahkan search agar lebih lengkap
    #[Url]
    public $search = '';

    // --- COMPUTED PROPERTIES ---

    #[Computed]
    public function categories()
    {
        return Category::orderBy('nama_kategori', 'asc')->get();
    }

    #[Computed]
    public function authors()
    {
        return Book::select('penulis')
            ->whereNotNull('penulis')
            ->distinct()
            ->orderBy('penulis', 'asc')
            ->pluck('penulis');
    }

    #[Computed]
    public function books()
    {
        // Eager load categories AND average rating
        $query = Book::query()
            ->with('categories')
            ->withAvg('reviews', 'rating'); // Ambil rata-rata rating

        // Filter Search (Judul/Penulis)
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('judul', 'like', '%' . $this->search . '%')
                  ->orWhere('penulis', 'like', '%' . $this->search . '%');
            });
        }

        // Filter Kategori (Many-to-Many)
        if ($this->selectedCategory) {
            $query->whereHas('categories', function (Builder $q) {
                $q->where('categories.id', $this->selectedCategory);
            });
        }

        // Filter Penulis
        if ($this->selectedAuthor) {
            $query->where('penulis', $this->selectedAuthor);
        }

        // Sorting
        match ($this->sortBy) {
            'a-z' => $query->orderBy('judul', 'asc'),
            'z-a' => $query->orderBy('judul', 'desc'),
            'rating' => $query->orderByDesc('reviews_avg_rating'), // Sorting berdasarkan rating
            default => $query->orderByDesc('created_at'),
        };

        return $query->paginate(12);
    }

    // --- ACTIONS ---

    public function resetFilters()
    {
        $this->selectedCategory = '';
        $this->selectedAuthor = '';
        $this->sortBy = 'terbaru';
        $this->search = '';
        $this->resetPage();
    }

    // Reset pagination saat filter berubah
    public function updated($property)
    {
        if (in_array($property, ['selectedCategory', 'selectedAuthor', 'sortBy', 'search'])) {
            $this->resetPage();
        }
    }

    public function getCoverUrl($gambarCover)
    {
        if ($gambarCover) {
            return Storage::url($gambarCover);
        }
        return 'https://placehold.co/400x600/1e293b/cbd5e1?text=No+Cover';
    }
}; ?>

<div class="min-h-screen bg-gray-950 text-gray-100">

    {{-- Header Section --}}
    <div class="relative overflow-hidden bg-gray-900 shadow-xl border-b border-gray-800">
        <div class="absolute inset-0 bg-gradient-to-r from-indigo-900/20 to-purple-900/20"></div>
        <div class="relative mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
            <h1 class="text-3xl font-extrabold tracking-tight text-white sm:text-4xl">
                Koleksi Pustaka
            </h1>
            <p class="mt-2 max-w-2xl text-lg text-gray-400">
                Temukan ribuan buku, jurnal, dan referensi akademik yang telah kami kurasi untuk Anda.
            </p>
        </div>
    </div>

    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">

        {{-- Sticky Filter Bar --}}
        <div class="sticky top-4 z-30 mb-8 rounded-xl border border-gray-700/50 bg-gray-900/80 p-4 shadow-lg backdrop-blur-md transition-all">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">

                {{-- Search Input --}}
                <div class="relative w-full lg:w-96">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                        <svg class="h-5 w-5 text-gray-500" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <input wire:model.live.debounce.300ms="search"
                           type="text"
                           class="block w-full rounded-lg border border-gray-700 bg-gray-800 py-2.5 pl-10 pr-3 text-sm text-gray-200 placeholder-gray-500 focus:border-indigo-500 focus:bg-gray-900 focus:ring-1 focus:ring-indigo-500"
                           placeholder="Cari judul atau penulis...">
                </div>

                {{-- Filters --}}
                <div class="flex flex-1 flex-col gap-3 sm:flex-row sm:items-center lg:justify-end">

                    {{-- Select Category --}}
                    <div class="relative">
                        <select wire:model.live="selectedCategory"
                                class="w-full appearance-none rounded-lg border border-gray-700 bg-gray-800 py-2.5 pl-3 pr-10 text-sm text-gray-200 focus:border-indigo-500 focus:ring-indigo-500 sm:w-48">
                            <option value="">Semua Kategori</option>
                            @foreach($this->categories as $category)
                                <option value="{{ $category->id }}">{{ $category->nama_kategori }}</option>
                            @endforeach
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-400">
                           <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </div>
                    </div>

                    {{-- Select Author --}}
                    <div class="relative">
                        <select wire:model.live="selectedAuthor"
                                class="w-full appearance-none rounded-lg border border-gray-700 bg-gray-800 py-2.5 pl-3 pr-10 text-sm text-gray-200 focus:border-indigo-500 focus:ring-indigo-500 sm:w-48">
                            <option value="">Semua Penulis</option>
                            @foreach($this->authors as $author)
                                <option value="{{ $author }}">{{ Str::limit($author, 20) }}</option>
                            @endforeach
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-400">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                         </div>
                    </div>

                    {{-- Select Sort --}}
                    <div class="relative">
                        <select wire:model.live="sortBy"
                                class="w-full appearance-none rounded-lg border border-gray-700 bg-gray-800 py-2.5 pl-3 pr-10 text-sm text-gray-200 focus:border-indigo-500 focus:ring-indigo-500 sm:w-48">
                            <option value="terbaru">Terbaru</option>
                            <option value="rating">Rating Tertinggi</option>
                            <option value="a-z">Judul (A - Z)</option>
                            <option value="z-a">Judul (Z - A)</option>
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-400">
                             <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 7.5 7.5 3m0 0L12 7.5M7.5 3v13.5m13.5 0L16.5 21m0 0L12 16.5m4.5 4.5V7.5" />
                              </svg>
                         </div>
                    </div>

                    {{-- Reset Button (Only shows if filtered) --}}
                    @if($selectedCategory || $selectedAuthor || $sortBy !== 'terbaru' || $search)
                        <button wire:click="resetFilters"
                                class="flex items-center justify-center gap-1 rounded-lg border border-red-900/50 bg-red-500/10 px-4 py-2.5 text-sm font-medium text-red-400 transition hover:bg-red-500/20 hover:text-red-300">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            <span class="hidden sm:inline">Reset</span>
                        </button>
                    @endif
                </div>
            </div>

            {{-- Loading Indicator Overlay --}}
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

        {{-- Book Grid --}}
        <div class="min-h-[500px]">
            <div class="grid grid-cols-2 gap-6 sm:grid-cols-3 md:grid-cols-4 xl:grid-cols-5">
                @forelse($this->books as $book)
                    <div class="group relative flex flex-col overflow-hidden rounded-2xl bg-gray-900 shadow-md ring-1 ring-white/5 transition-all duration-300 hover:-translate-y-2 hover:shadow-2xl hover:shadow-indigo-500/10">

                        {{-- Cover Image --}}
                        <div class="relative aspect-[2/3] w-full overflow-hidden">
                            <img src="{{ $this->getCoverUrl($book->gambar_cover) }}"
                                 alt="{{ $book->judul }}"
                                 loading="lazy"
                                 class="h-full w-full object-cover transition-transform duration-700 group-hover:scale-110">

                            {{-- Dark Gradient Overlay --}}
                            <div class="absolute inset-0 bg-gradient-to-t from-gray-950 via-gray-950/20 to-transparent opacity-60 transition-opacity group-hover:opacity-80"></div>

                            {{-- Badges --}}
                            <div class="absolute left-2 top-2 flex flex-wrap gap-1">
                                @if($book->categories->isNotEmpty())
                                    <span class="rounded bg-black/60 px-2 py-1 text-[10px] font-bold uppercase tracking-wider text-white backdrop-blur-md">
                                        {{ $book->categories->first()->nama_kategori }}
                                    </span>
                                @endif
                            </div>

                            {{-- Hover Button --}}
                            <div class="absolute inset-0 flex items-center justify-center opacity-0 transition-opacity duration-300 group-hover:opacity-100">
                                <a href="{{ route('user.buku.detail', $book->slug) }}"
                                   wire:navigate
                                   class="translate-y-4 rounded-full bg-indigo-600 px-5 py-2 text-xs font-bold text-white shadow-lg transition-all duration-300 group-hover:translate-y-0 hover:bg-indigo-500">
                                    Lihat Detail
                                </a>
                            </div>
                        </div>

                        {{-- Card Content --}}
                        <div class="flex flex-1 flex-col justify-between p-4">
                            <div>
                                <h3 class="line-clamp-2 text-sm font-bold leading-snug text-gray-100 transition-colors group-hover:text-indigo-400" title="{{ $book->judul }}">
                                    {{ $book->judul }}
                                </h3>
                                <p class="mt-1 line-clamp-1 text-xs text-gray-500">
                                    {{ $book->penulis ?? 'Tanpa Penulis' }}
                                </p>
                            </div>

                            {{-- Rating Row --}}
                            <div class="mt-3 flex items-center justify-between border-t border-gray-800 pt-3">
                                <div class="flex items-center gap-1">
                                    <svg class="h-3 w-3 text-yellow-500" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                    </svg>
                                    <span class="text-xs font-medium text-gray-300">
                                        {{ number_format($book->reviews_avg_rating, 1) }}
                                    </span>
                                </div>
                                <span class="text-[10px] text-gray-600">
                                    {{ $book->created_at->diffForHumans() }}
                                </span>
                            </div>
                        </div>
                    </div>
                @empty
                    {{-- Modern Empty State --}}
                    <div class="col-span-full flex flex-col items-center justify-center rounded-2xl border border-dashed border-gray-800 bg-gray-900/50 py-20 text-center">
                        <div class="relative mb-4 flex h-20 w-20 items-center justify-center rounded-full bg-gray-800 shadow-inner">
                            <svg class="h-10 w-10 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                            </svg>
                            {{-- Magnifying glass decoration --}}
                            <div class="absolute -bottom-1 -right-1 rounded-full bg-gray-900 p-1">
                                <div class="rounded-full bg-indigo-600 p-1.5">
                                    <svg class="h-4 w-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>
                        <h3 class="text-xl font-bold text-white">Tidak ada buku ditemukan</h3>
                        <p class="mt-2 max-w-sm text-sm text-gray-400">
                            Coba ubah kata kunci pencarian atau atur ulang filter kategori dan penulis.
                        </p>
                        <button wire:click="resetFilters" class="mt-6 rounded-lg bg-indigo-600 px-6 py-2.5 text-sm font-semibold text-white transition hover:bg-indigo-500">
                            Hapus Semua Filter
                        </button>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Pagination --}}
        <div class="mt-12 border-t border-gray-800 pt-8">
            {{ $this->books->links(data: ['scrollTo' => false]) }}
        </div>

    </div>
</div>
