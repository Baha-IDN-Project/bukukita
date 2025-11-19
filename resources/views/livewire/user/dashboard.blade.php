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

    /**
     * Inisialisasi komponen dengan mengambil data buku dan kategori.
     */
    public function mount(): void
    {
        // 1. Mengambil 10 buku terbaru yang ditambahkan
        $this->newestBooks = Book::with('category')
                                ->orderByDesc('created_at')
                                ->take(10)
                                ->get();

        // 2. Mengambil 5 buku dengan rating tertinggi
        $this->highestRatedBooks = Book::with('category')
                                       ->withAvg('reviews', 'rating')
                                       ->orderByDesc('reviews_avg_rating')
                                       ->take(5)
                                       ->get();

        // 3. Mengambil Kategori dari Backend (Bukan Popularitas, tapi Terbaru/Urutan)
        $this->categories = Category::orderByDesc('created_at')
                                        ->take(6)
                                        ->get();
    }

    /**
     * Helper untuk mendapatkan URL gambar cover.
     */
    public function getCoverUrl($gambarCover)
    {
        if ($gambarCover) {
            return Storage::url($gambarCover);
        }
        return 'https://placehold.co/300x400/34495e/ffffff?text=E-Book';
    }

    /**
     * Helper untuk warna background kategori dinamis (Opsional, agar bervariasi)
     */
    public function getCategoryColor($index)
    {
        $colors = ['e67e22', '2980b9', 'c0392b', '8e44ad', '16a085', 'f1c40f'];
        return $colors[$index % count($colors)];
    }

    /**
     * Memotong deskripsi buku.
     */
    public function getShortDescription(string $description, int $limit = 150): string
    {
        if (empty($description)) {
            return 'Deskripsi buku ini belum tersedia.';
        }
        $cleanDescription = strip_tags($description);

        if (strlen($cleanDescription) > $limit) {
            return substr($cleanDescription, 0, $limit) . '...';
        }

        return $cleanDescription;
    }
}; ?>

<div>
    {{-- Konten Halaman --}}
    <div class="flex h-full w-full flex-1 flex-col gap-8 rounded-xl">

        <div class="relative aspect-[16/5] w-full overflow-hidden rounded-xl shadow-lg">
            <img src="https://placehold.co/1200x375/6366f1/ffffff?text=Selamat+Datang+di+Perpustakaan"
                alt="Welcome Banner" class="absolute inset-0 size-full object-cover">
            <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent"></div>
            <div class="absolute bottom-6 left-6 text-white">
                <h2 class="text-3xl font-bold">Koleksi Terbaru Bulan Ini</h2>
                <p class="mt-1 text-lg">Temukan buku-buku terbaik pilihan editor kami.</p>
            </div>
        </div>

        <div>
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-xl font-semibold text-gray-100">Buku Terbaru</h2>
                <a href="#" class="text-sm font-medium text-indigo-400 hover:text-indigo-300">
                    Lihat Semua
                </a>
            </div>
            <div class="flex space-x-4 overflow-x-auto pb-4 scrollbar-hide">
                @forelse ($newestBooks as $book)
                    <div class="w-40 flex-shrink-0 group">
                        <div class="relative aspect-[3/4] w-full overflow-hidden rounded-lg bg-gray-800 shadow-lg transition-all duration-300 group-hover:-translate-y-1 group-hover:shadow-xl group-hover:shadow-indigo-500/10">
                            <img src="{{ $this->getCoverUrl($book->gambar_cover) }}"
                                alt="Cover {{ $book->judul }}"
                                {{-- Animasi scale 110 --}}
                                class="size-full object-cover transition-transform duration-500 group-hover:scale-110"
                                onerror="this.src='https://placehold.co/300x400/34495e/ffffff?text=E-Book'">

                            <div class="absolute inset-0 bg-black/80 opacity-0 transition-opacity duration-300 group-hover:opacity-100 flex flex-col items-start justify-end p-3">
                                <p class="text-white text-xs line-clamp-6">
                                    {{ $this->getShortDescription($book->deskripsi ?? 'Deskripsi buku ini belum tersedia.') }}
                                </p>
                                <a href="{{ route('user.buku.detail', $book->slug) }}"
                                    wire:navigate
                                    {{-- Animasi tombol muncul dari bawah (translate-y-4 ke translate-y-0) --}}
                                    class="mt-2 bg-indigo-600 hover:bg-indigo-500 text-white px-3 py-1.5 rounded-full text-xs font-medium transform translate-y-4 group-hover:translate-y-0 transition-transform duration-300 shadow-lg">
                                    Lihat Detail
                                </a>
                            </div>

                        </div>
                        <h3 class="mt-2 truncate font-medium text-gray-100" title="{{ $book->judul }}">
                            {{ $book->judul }}
                        </h3>
                        <p class="text-sm text-gray-400">
                            {{ $book->penulis ?? 'N/A' }}
                        </p>
                    </div>
                @empty
                    <div class="w-full p-4 text-center text-gray-500 bg-gray-800/50 rounded-lg">
                        Belum ada buku terbaru.
                    </div>
                @endforelse
            </div>
        </div>

        <div>
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-xl font-semibold text-gray-100">Rating Tertinggi</h2>
                <a href="#" class="text-sm font-medium text-indigo-400 hover:text-indigo-300">
                    Lihat Semua
                </a>
            </div>
            <div class="flex space-x-4 overflow-x-auto pb-4 scrollbar-hide">
                @forelse ($highestRatedBooks as $book)
                    <div class="w-40 flex-shrink-0 group">
                        <div class="relative aspect-[3/4] w-full overflow-hidden rounded-lg bg-gray-800 shadow-lg transition-all duration-300 group-hover:-translate-y-1 group-hover:shadow-xl group-hover:shadow-indigo-500/10">
                            <img src="{{ $this->getCoverUrl($book->gambar_cover) }}"
                                alt="Cover {{ $book->judul }}"
                                {{-- Animasi scale 110 --}}
                                class="size-full object-cover transition-transform duration-500 group-hover:scale-110"
                                onerror="this.src='https://placehold.co/300x400/34495e/ffffff?text=E-Book'">

                            <div class="absolute inset-0 bg-black/80 opacity-0 transition-opacity duration-300 group-hover:opacity-100 flex flex-col items-start justify-end p-3">
                                <p class="text-white text-xs line-clamp-6">
                                    {{ $this->getShortDescription($book->deskripsi ?? 'Deskripsi buku ini belum tersedia.') }}
                                </p>
                                <a href="{{ route('user.buku.detail', $book->slug) }}"
                                    wire:navigate
                                    {{-- Animasi tombol muncul dari bawah (translate-y-4 ke translate-y-0) --}}
                                    class="mt-2 bg-indigo-600 hover:bg-indigo-500 text-white px-3 py-1.5 rounded-full text-xs font-medium transform translate-y-4 group-hover:translate-y-0 transition-transform duration-300 shadow-lg">
                                    Lihat Detail
                                </a>
                            </div>

                        </div>
                        <h3 class="mt-2 truncate font-medium text-gray-100" title="{{ $book->judul }}">
                            {{ $book->judul }}
                        </h3>
                        <p class="text-sm text-gray-400">
                            {{ $book->penulis ?? 'N/A' }}
                        </p>
                        <div class="flex items-center mt-1">
                             <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="#DAA520" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-star w-4 h-4">
                                <path d="M11.525 2.295a.53.53 0 0 1 .95 0l2.31 4.679a2.123 2.123 0 0 0 1.595 1.16l5.166.756a.53.53 0 0 1 .294.904l-3.736 3.638a2.123 2.123 0 0 0-.611 1.878l.882 5.14a.53.53 0 0 1-.771.56l-4.618-2.428a2.122 2.122 0 0 0-1.973 0L6.396 21.01a.53.53 0 0 1-.77-.56l.881-5.139a2.122 2.122 0 0 0-.611-1.879L2.16 9.795a.53.53 0 0 1 .294-.906l5.165-.755a2.122 2.122 0 0 0 1.597-1.16z" fill="#DAA520"/>
                            </svg>
                            <span class="text-gray-200 font-bold text-sm ml-1">
                                {{ number_format($book->reviews_avg_rating, 1) }}
                            </span>
                        </div>
                    </div>
                @empty
                    <div class="w-full p-4 text-center text-gray-500 bg-gray-800/50 rounded-lg">
                        Belum ada rating yang tersedia.
                    </div>
                @endforelse
            </div>
        </div>

        <div>
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-xl font-semibold text-gray-100">Jelajahi Kategori</h2>
                <a href="#" class="text-sm font-medium text-indigo-400 hover:text-indigo-300">
                    Lihat Semua
                </a>
            </div>

            <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6">

                @forelse ($categories as $index => $category)
                    {{-- Animasi Kategori tetap 'hover:scale-105' --}}
                    <a href="#" class="relative aspect-video overflow-hidden rounded-lg shadow-md transition-transform duration-300 hover:scale-105 group">
                        <img src="https://placehold.co/300x200/{{ $this->getCategoryColor($index) }}/ffffff?text={{ urlencode($category->nama_kategori) }}"
                            alt="{{ $category->nama_kategori }}"
                            class="size-full object-cover">

                        {{-- Overlay Gradient --}}
                        <div class="absolute inset-0 bg-black/40 group-hover:bg-black/50 transition-colors"></div>

                        {{-- Text Kategori --}}
                        <h3 class="absolute bottom-2 left-2 font-semibold text-white tracking-wide drop-shadow-md">
                            {{ $category->nama_kategori }}
                        </h3>
                    </a>
                @empty
                    <div class="col-span-full p-8 text-center text-gray-400 bg-gray-800/50 rounded-lg border border-dashed border-gray-700">
                        <p>Belum ada kategori yang ditambahkan.</p>
                    </div>
                @endforelse

            </div>
        </div>

    </div>
</div>
