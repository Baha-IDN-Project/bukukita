<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use App\Models\Book;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

// Menggunakan layout utama pengguna, sesuai dengan layout di kode contoh Anda
new #[Layout('components.layouts.user')]
class extends Component {

    public Collection $newestBooks;
    public Collection $highestRatedBooks;

    /**
     * Inisialisasi komponen dengan mengambil data buku.
     * (Logika ini tidak diubah, sesuai permintaan)
     */
    public function mount(): void
    {
        // 1. Mengambil 10 buku terbaru yang ditambahkan
        $this->newestBooks = Book::with('category')
                                ->orderByDesc('created_at')
                                ->take(10)
                                ->get();

        // 2. Mengambil 5 buku dengan rating tertinggi
        // Menggunakan query yang Anda sediakan
        $this->highestRatedBooks = Book::with('category')
                                      ->withAvg('reviews', 'rating')
                                      ->orderByDesc('reviews_avg_rating')
                                      ->take(5)
                                      ->get();
    }

    /**
     * Helper untuk mendapatkan URL gambar cover.
     * (Logika ini tidak diubah, sesuai permintaan)
     */
    public function getCoverUrl($gambarCover)
    {
        if ($gambarCover) {
            // Menggunakan Storage::url untuk mendapatkan path publik
            return Storage::url($gambarCover);
        }

        // Gambar fallback jika buku tidak memiliki cover
        // Disesuaikan agar cocok dengan rasio 300x400
        return 'https://placehold.co/300x400/34495e/ffffff?text=E-Book';
    }
}; ?>

{{--
  Ini adalah bagian Blade/HTML baru, menggunakan struktur yang Anda berikan,
  digabungkan dengan data dinamis dari logika di atas.
  Saya mempertahankan tema gelap dari file Anda sebelumnya.
--}}
<div class="p-6 md:p-10 bg-gray-900 min-h-screen">

    {{-- Konten Halaman --}}
    <div class="flex h-full w-full flex-1 flex-col gap-8 rounded-xl">

        <!-- 1. Bagian Banner/Hero Utama -->
        <!-- Ini diambil langsung dari kode contoh Anda -->
        <div class="relative aspect-[16/5] w-full overflow-hidden rounded-xl shadow-lg">
            <img src="https://placehold.co/1200x375/6366f1/ffffff?text=Selamat+Datang+di+Perpustakaan"
                alt="Welcome Banner" class="absolute inset-0 size-full object-cover">
            <!-- Overlay gradien untuk memperjelas teks -->
            <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent"></div>
            <!-- Teks di atas banner -->
            <div class="absolute bottom-6 left-6 text-white">
                <h2 class="text-3xl font-bold">Koleksi Terbaru Bulan Ini</h2>
                <p class="mt-1 text-lg">Temukan buku-buku terbaik pilihan editor kami.</p>
            </div>
        </div>

        <!-- 2. Rak Buku Saya (Diganti dengan: Buku Terbaru Ditambahkan) -->
        <div>
            <!-- Judul Rak Buku -->
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-xl font-semibold text-gray-100">Buku Terbaru</h2>
                <a href="#"
                    class="text-sm font-medium text-indigo-400 hover:text-indigo-300">
                    Lihat Semua
                </a>
            </div>
            <!-- Kontainer Rak Buku (Horizontal Scroll) -->
            <div class="flex space-x-4 overflow-x-auto pb-4">

                {{-- Data dinamis dari $newestBooks --}}
                @forelse ($newestBooks as $book)
                    <div class="w-40 flex-shrink-0">
                        <!-- Cover Buku: aspect-[3/4] adalah rasio standar cover buku -->
                        <div class="aspect-[3/4] overflow-hidden rounded-lg shadow-md">
                            <img src="{{ $this->getCoverUrl($book->gambar_cover) }}"
                                alt="Cover {{ $book->judul }}"
                                class="size-full object-cover transition-transform duration-300 hover:scale-105"
                                onerror="this.src='https://placehold.co/300x400/34495e/ffffff?text=E-Book'">
                        </div>
                        <!-- Info Buku -->
                        <h3 class="mt-2 truncate font-medium text-gray-100" title="{{ $book->judul }}">
                            {{ $book->judul }}
                        </h3>
                        <p class="text-sm text-gray-400">
                            {{ $book->penulis ?? 'N/A' }}
                        </p>
                    </div>
                @empty
                    <p class="text-gray-400">Belum ada buku terbaru.</p>
                @endforelse

            </div>
        </div>

        <!-- 3. Rak Koleksi Pilihan (Diganti dengan: Rating Tertinggi) -->
        <div>
            <!-- Judul Rak Buku -->
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-xl font-semibold text-gray-100">Rating Tertinggi</h2>
                <a href="#"
                    class="text-sm font-medium text-indigo-400 hover:text-indigo-300">
                    Lihat Semua
                </a>
            </div>

            <!-- Kontainer Rak Buku (Horizontal Scroll) -->
            <div class="flex space-x-4 overflow-x-auto pb-4">

                {{-- Data dinamis dari $highestRatedBooks --}}
                @forelse ($highestRatedBooks as $book)
                    <div class="w-40 flex-shrink-0">
                        <div class="aspect-[3/4] overflow-hidden rounded-lg shadow-md">
                            <img src="{{ $this->getCoverUrl($book->gambar_cover) }}"
                                alt="Cover {{ $book->judul }}"
                                class="size-full object-cover transition-transform duration-300 hover:scale-105"
                                onerror="this.src='httpshttps://placehold.co/300x400/34495e/ffffff?text=E-Book'">
                        </div>
                        <h3 class="mt-2 truncate font-medium text-gray-100" title="{{ $book->judul }}">
                            {{ $book->judul }}
                        </h3>
                        <p class="text-sm text-gray-400">
                            {{ $book->penulis ?? 'N/A' }}
                        </p>
                        {{-- Info Rating --}}
                        <div class="flex items-center mt-1">
                            <svg class="w-4 h-4 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.87 5.766a1 1 0 00.95.69h6.05c.969 0 1.372 1.24.588 1.81l-4.89 3.55a1 1 0 00-.364 1.118l1.87 5.766c.3.921-.755 1.688-1.54 1.118l-4.89-3.55a1 1 0 00-1.176 0l-4.89 3.55c-.784.57-1.838-.197-1.54-1.118l1.87-5.766a1 1 0 00-.364-1.118L.587 11.193c-.784-.57-.38-1.81.588-1.81h6.05a1 1 0 00.95-.69L9.049 2.927z" />
                            </svg>
                            <span class="text-gray-200 font-bold text-sm ml-1">
                                {{ number_format($book->reviews_avg_rating, 1) }}
                            </span>
                        </div>
                    </div>
                @empty
                    <p class="text-gray-400">Belum ada buku yang memiliki rating.</p>
                @endforelse

            </div>
        </div>

        <!-- 4. Rak Kategori (Data Statis dari contoh Anda) -->
        <div>
            <!-- Judul Rak Buku -->
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-xl font-semibold text-gray-100">Kategori Populer</h2>
            </div>
            <!-- Kontainer Kategori: Menggunakan Grid -->
            <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6">

                <!-- Kategori 1 -->
                <a href="#" class="relative aspect-video overflow-hidden rounded-lg shadow-md transition-transform duration-300 hover:scale-105">
                    <img src="https://placehold.co/300x200/e67e22/ffffff?text=Fiksi+Ilmiah" alt="Kategori" class="size-full object-cover">
                    <div class="absolute inset-0 bg-black/40"></div>
                    <h3 class="absolute bottom-2 left-2 font-semibold text-white">Fiksi Ilmiah</h3>
                </a>
                <!-- Kategori 2 -->
                <a href="#" class="relative aspect-video overflow-hidden rounded-lg shadow-md transition-transform duration-300 hover:scale-105">
                    <img src="https://placehold.co/300x200/2980b9/ffffff?text=Bisnis" alt="Kategori" class="size-full object-cover">
                    <div class="absolute inset-0 bg-black/40"></div>
                    <h3 class="absolute bottom-2 left-2 font-semibold text-white">Bisnis</h3>
                </a>
                <!-- Kategori 3 -->
                <a href="#" class="relative aspect-video overflow-hidden rounded-lg shadow-md transition-transform duration-300 hover:scale-105">
                    <img src="https://placehold.co/300x200/c0392b/ffffff?text=Sejarah" alt="Kategori" class="size-full object-cover">
                    <div class="absolute inset-0 bg-black/40"></div>
                    <h3 class="absolute bottom-2 left-2 font-semibold text-white">Sejarah</h3>
                </a>
                <!-- Kategori 4 -->
                <a href="#" class="relative aspect-video overflow-hidden rounded-lg shadow-md transition-transform duration-300 hover:scale-105">
                    <img src="https://placehold.co/300x200/8e44ad/ffffff?text=Romansa" alt="Kategori" class="size-full object-cover">
                    <div class="absolute inset-0 bg-black/40"></div>
                    <h3 class="absolute bottom-2 left-2 font-semibold text-white">Romansa</h3>
                </a>
                <!-- Kategori 5 (Contoh Tambahan) -->
                <a href="#" class="relative aspect-video overflow-hidden rounded-lg shadow-md transition-transform duration-300 hover:scale-105">
                    <img src="https://placehold.co/300x200/16a085/ffffff?text=Self-Help" alt="Kategori" class="size-full object-cover">
                    <div class="absolute inset-0 bg-black/40"></div>
                    <h3 class="absolute bottom-2 left-2 font-semibold text-white">Self-Help</h3>
                </a>
                <!-- Kategori 6 (Contoh Tambahan) -->
                <a href="#" class="relative aspect-video overflow-hidden rounded-lg shadow-md transition-transform duration-300 hover:scale-105">
                    <img src="https://placehold.co/300x200/f1c40f/ffffff?text=Komik" alt="Kategori" class="size-full object-cover">
                    <div class="absolute inset-0 bg-black/40"></div>
                    <h3 class="absolute bottom-2 left-2 font-semibold text-white">Komik</h3>
                </a>

            </div>
        </div>

    </div>
</div>
