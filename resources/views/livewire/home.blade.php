<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use App\Models\Book;
use App\Models\Category; // Import Model Category
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

new #[Layout('components.layouts.public')]
class extends Component {

    public Collection $newestBooks;
    public Collection $highestRatedBooks;
    public Collection $categories; // Properti baru untuk kategori

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
        // Mengambil 6 kategori untuk layout grid
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
}; ?>

<div>
    {{-- Konten Halaman --}}
    <div class="flex h-full w-full flex-1 flex-col gap-8 rounded-xl">

        <!-- 1. Bagian Banner/Hero Utama -->
        <div class="relative aspect-[16/5] w-full overflow-hidden rounded-xl shadow-lg">
            <img src="https://placehold.co/1200x375/6366f1/ffffff?text=Selamat+Datang+di+Perpustakaan"
                alt="Welcome Banner" class="absolute inset-0 size-full object-cover">
            <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent"></div>
            <div class="absolute bottom-6 left-6 text-white">
                <h2 class="text-3xl font-bold">Koleksi Terbaru Bulan Ini</h2>
                <p class="mt-1 text-lg">Temukan buku-buku terbaik pilihan editor kami.</p>
            </div>
        </div>

        <!-- 2. Rak Buku Terbaru -->
        <div>
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-xl font-semibold text-gray-800">Buku Terbaru</h2>
                <a href="#" class="text-sm font-medium text-indigo-400 hover:text-indigo-300">
                    Daftar Untuk Lihat Semua
                </a>
            </div>
            <div class="flex space-x-4 overflow-x-auto pb-4 scrollbar-hide">
                @forelse ($newestBooks as $book)
                    <div class="w-40 flex-shrink-0">
                        <div class="aspect-[3/4] overflow-hidden rounded-lg shadow-md relative group">
                            <img src="{{ $this->getCoverUrl($book->gambar_cover) }}"
                                alt="Cover {{ $book->judul }}"
                                class="size-full object-cover transition-transform duration-300 group-hover:scale-105"
                                onerror="this.src='https://placehold.co/300x400/34495e/ffffff?text=E-Book'">
                            <!-- Overlay hover effect (Flux style) -->
                            <div class="absolute inset-0 bg-black/0 transition-colors duration-300 group-hover:bg-black/10"></div>
                        </div>
                        <h3 class="mt-2 truncate font-medium text-gray-800" title="{{ $book->judul }}">
                            {{ $book->judul }}
                        </h3>
                        <p class="text-sm text-gray-600">
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

        <!-- 3. Rak Rating Tertinggi -->
        <div>
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-xl font-semibold text-gray-800">Rating Tertinggi</h2>
                <a href="#" class="text-sm font-medium text-indigo-400 hover:text-indigo-300">
                    Daftar Untuk Lihat Semua
                </a>
            </div>
            <div class="flex space-x-4 overflow-x-auto pb-4 scrollbar-hide">
                @forelse ($highestRatedBooks as $book)
                    <div class="w-40 flex-shrink-0">
                        <div class="aspect-[3/4] overflow-hidden rounded-lg shadow-md relative group">
                            <img src="{{ $this->getCoverUrl($book->gambar_cover) }}"
                                alt="Cover {{ $book->judul }}"
                                class="size-full object-cover transition-transform duration-300 group-hover:scale-105"
                                onerror="this.src='https://placehold.co/300x400/34495e/ffffff?text=E-Book'">
                        </div>
                        <h3 class="mt-2 truncate font-medium text-gray-800" title="{{ $book->judul }}">
                            {{ $book->judul }}
                        </h3>
                        <p class="text-sm text-gray-600">
                            {{ $book->penulis ?? 'N/A' }}
                        </p>
                        <div class="flex items-center mt-1">
                            <svg class="w-4 h-4 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.87 5.766a1 1 0 00.95.69h6.05c.969 0 1.372 1.24.588 1.81l-4.89 3.55a1 1 0 00-.364 1.118l1.87 5.766c.3.921-.755 1.688-1.54 1.118l-4.89-3.55a1 1 0 00-1.176 0l-4.89 3.55c-.784.57-1.838-.197-1.54-1.118l1.87-5.766a1 1 0 00-.364-1.118L.587 11.193c-.784-.57-.38-1.81.588-1.81h6.05a1 1 0 00.95-.69L9.049 2.927z" />
                            </svg>
                            <span class="text-gray-400 font-bold text-sm ml-1">
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

        <!-- 4. Rak Kategori (Dinamis dari Backend) -->
        <div>
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-xl font-semibold text-gray-800">Jelajahi Kategori</h2>
                {{-- Link ini bisa diarahkan ke halaman list kategori jika ada --}}
                <a href="#" class="text-sm font-medium text-indigo-400 hover:text-indigo-300">
                    Daftar Untuk Lihat Semua
                </a>
            </div>

            {{-- Grid Kategori Dinamis --}}
            <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6">

                @forelse ($categories as $index => $category)
                    <a href="#" class="relative aspect-video overflow-hidden rounded-lg shadow-md transition-transform duration-300 hover:scale-105 group">
                        {{--
                           Membangkitkan gambar placeholder dinamis berdasarkan nama kategori.
                           Menggunakan warna yang berbeda-beda agar tidak monoton.
                        --}}
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
                     <div class="col-span-full p-8 text-center text-gray-600 bg-gray-800/50 rounded-lg border border-dashed border-gray-700">
                        <p>Belum ada kategori yang ditambahkan.</p>
                    </div>
                @endforelse

            </div>
        </div>

    </div>
</div>
