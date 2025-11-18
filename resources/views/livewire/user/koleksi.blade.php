<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\WithPagination;
use App\Models\Book;
use App\Models\Category;
use Illuminate\Support\Facades\Storage;

new #[Layout('components.layouts.user')]
class extends Component {

    use WithPagination;

    // --- STATE ---
    // #[Url] membuat filter tetap ada di URL (bookmarkable)
    #[Url]
    public $selectedCategory = '';

    #[Url]
    public $selectedAuthor = '';

    #[Url]
    public $sortBy = 'terbaru';

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
        $query = Book::query()->with('category');

        // Filter Kategori
        if ($this->selectedCategory) {
            $query->where('category_id', $this->selectedCategory);
        }

        // Filter Penulis
        if ($this->selectedAuthor) {
            $query->where('penulis', $this->selectedAuthor);
        }

        // Sorting
        match ($this->sortBy) {
            'a-z' => $query->orderBy('judul', 'asc'),
            'z-a' => $query->orderBy('judul', 'desc'),
            default => $query->latest(),
        };

        return $query->paginate(12);
    }

    // --- ACTIONS ---

    public function resetFilters()
    {
        $this->selectedCategory = '';
        $this->selectedAuthor = '';
        $this->sortBy = 'terbaru';
        $this->resetPage();
    }

    public function getCoverUrl($gambarCover)
    {
        if ($gambarCover) {
            return Storage::url($gambarCover);
        }
        return 'https://placehold.co/300x400/34495e/ffffff?text=E-Book';
    }
}; ?>

<div>
    <div class="flex h-full w-full flex-1 flex-col gap-8 rounded-xl">

        <div class="rounded-xl bg-gray-900/50 p-6 shadow-sm border border-gray-800">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
                <div>
                    <h2 class="text-2xl font-bold text-white">Koleksi Pustaka</h2>
                    <p class="text-sm text-gray-400 mt-1">Temukan buku favoritmu dari koleksi kami.</p>
                </div>

                @if($selectedCategory || $selectedAuthor || $sortBy !== 'terbaru')
                    <button wire:click="resetFilters"
                            class="text-sm text-red-400 hover:text-red-300 flex items-center gap-1 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        Reset Filter
                    </button>
                @endif
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-400 mb-1 uppercase tracking-wider">Kategori</label>
                    <select wire:model.live="selectedCategory"
                            class="w-full bg-gray-800 text-gray-200 border border-gray-700 rounded-lg py-2.5 px-3 focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all">
                        <option value="">Semua Kategori</option>
                        @foreach($this->categories as $category)
                            <option value="{{ $category->id }}">{{ $category->nama_kategori }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-400 mb-1 uppercase tracking-wider">Penulis</label>
                    <select wire:model.live="selectedAuthor"
                            class="w-full bg-gray-800 text-gray-200 border border-gray-700 rounded-lg py-2.5 px-3 focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all">
                        <option value="">Semua Penulis</option>
                        @foreach($this->authors as $author)
                            <option value="{{ $author }}">{{ $author }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-400 mb-1 uppercase tracking-wider">Urutkan</label>
                    <select wire:model.live="sortBy"
                            class="w-full bg-gray-800 text-gray-200 border border-gray-700 rounded-lg py-2.5 px-3 focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all">
                        <option value="terbaru">Terbaru Ditambahkan</option>
                        <option value="a-z">Judul (A - Z)</option>
                        <option value="z-a">Judul (Z - A)</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="min-h-[400px]">
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-4 sm:gap-6">
                @forelse($this->books as $book)
                    <div class="group flex flex-col">
                        <div class="relative aspect-[3/4] w-full overflow-hidden rounded-xl bg-gray-800 shadow-lg transition-all duration-300 group-hover:-translate-y-1 group-hover:shadow-xl group-hover:shadow-indigo-500/10">
                            <img src="{{ $this->getCoverUrl($book->gambar_cover) }}"
                                 alt="{{ $book->judul }}"
                                 class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-110">

                            <div class="absolute inset-0 bg-black/60 opacity-0 transition-opacity duration-300 group-hover:opacity-100 flex items-center justify-center">
                                <button class="bg-indigo-600 hover:bg-indigo-500 text-white px-4 py-2 rounded-full text-sm font-medium transform translate-y-4 group-hover:translate-y-0 transition-transform duration-300">
                                    Lihat Detail
                                </button>
                            </div>

                            <span class="absolute top-2 right-2 bg-black/50 backdrop-blur-sm text-white text-[10px] px-2 py-0.5 rounded-full border border-white/10">
                                {{ $book->category->nama_kategori }}
                            </span>
                        </div>

                        <div class="mt-3 px-1">
                            <h3 class="text-sm font-semibold text-gray-200 line-clamp-2 leading-tight group-hover:text-indigo-400 transition-colors" title="{{ $book->judul }}">
                                {{ $book->judul }}
                            </h3>
                            <p class="text-xs text-gray-500 mt-1">
                                {{ $book->penulis ?? 'Unknown' }}
                            </p>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full flex flex-col items-center justify-center py-16 px-4 text-center border border-dashed border-gray-700 rounded-xl bg-gray-900/30">
                        <div class="bg-gray-800 p-4 rounded-full mb-4">
                            <svg class="w-8 h-8 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                        </div>
                        <h3 class="text-lg font-medium text-white">Tidak ada buku ditemukan</h3>
                        <p class="text-gray-400 mt-1 max-w-sm">
                            Maaf, kami tidak dapat menemukan buku dengan kriteria pencarian tersebut.
                        </p>
                        <button wire:click="resetFilters" class="mt-6 px-4 py-2 bg-gray-800 hover:bg-gray-700 text-white rounded-lg text-sm font-medium transition-colors border border-gray-700">
                            Hapus Filter
                        </button>
                    </div>
                @endforelse
            </div>
        </div>

        <div class="mt-auto pt-6 border-t border-gray-800">
            {{ $this->books->links(data: ['scrollTo' => false]) }}
        </div>

    </div>
</div>
