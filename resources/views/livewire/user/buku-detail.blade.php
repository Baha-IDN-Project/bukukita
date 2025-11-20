<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Computed;
use App\Models\Book;
use App\Models\Peminjaman;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

new #[Layout('components.layouts.user')]
class extends Component {

    // Properti Book otomatis diisi oleh Route Model Binding
    public Book $book;

    #[Computed]
    public function reviews()
    {
        return $this->book->reviews()->with('user')->latest()->get();
    }

    #[Computed]
    public function relatedBooks()
    {
        // Rekomendasi buku lain dalam kategori yang sama
        return Book::where('category_id', $this->book->category_id)
            ->where('id', '!=', $this->book->id)
            ->take(4)
            ->get();
    }

    #[Computed]
    public function currentStatus()
    {
        // Cek apakah user sedang meminjam buku ini
        $loan = Peminjaman::where('user_id', Auth::id())
            ->where('book_id', $this->book->id)
            ->whereIn('status', ['pending', 'dipinjam'])
            ->first();

        if ($loan) {
            return $loan->status; // Mengembalikan 'pending' atau 'dipinjam'
        }

        return null; // Belum meminjam
    }

    // Hitung stok tersedia sekali untuk digunakan di Blade
    #[Computed]
    public function stokTersedia()
    {
        return $this->book->lisensi - $this->book->jumlah_dipinjam;
    }

    public function pinjamBuku()
    {
        // 1. Validasi: Apakah stok (lisensi) habis?
        $stokTersedia = $this->stokTersedia; // Menggunakan computed property

        if ($stokTersedia <= 0) {
            session()->flash('error', 'Maaf, stok buku ini sedang habis dipinjam (semua lisensi terpakai).');
            $this->redirect(route('user.koleksi'), navigate: true); // Redirect ke halaman koleksi setelah notif error
            return;
        }

        // 2. Validasi: Apakah user sudah meminjam?
        if ($this->currentStatus) {
            session()->flash('warning', 'Anda sudah memiliki permintaan peminjaman yang aktif untuk buku ini: ' . ucfirst($this->currentStatus));
            $this->redirect(route('user.rak'), navigate: true);
            return;
        }

        // 3. Proses Peminjaman (Default status: 'pending')
        Peminjaman::create([
            'user_id' => Auth::id(),
            'book_id' => $this->book->id,
            'tanggal_pinjam' => null, // Admin yang akan mengisi saat approve
            'tanggal_harus_kembali' => null, // Admin yang akan mengisi saat approve
            'status' => 'pending', // Wajib pending, tunggu approve admin
        ]);

        // Refresh halaman / Tampilkan notifikasi
        session()->flash('success', 'Permintaan peminjaman berhasil dibuat. Menunggu persetujuan admin.');
        $this->redirect(route('user.rak'), navigate: true);
    }

    public function getCoverUrl($gambarCover)
    {
        return $gambarCover ? Storage::url($gambarCover) : 'https://placehold.co/300x400/34495e/ffffff?text=No+Cover';
    }
}; ?>

<div class="flex flex-col gap-8 pb-12">

    {{-- Breadcrumb --}}
    <nav class="flex text-sm text-gray-400">
        <a href="{{ route('user.koleksi') }}" class="hover:text-indigo-400 transition-colors">Koleksi</a>
        <span class="mx-2">/</span>
        <span class="text-gray-200 font-medium truncate">{{ $this->book->judul }}</span>
    </nav>

    {{-- Bagian Utama Detail Buku --}}
    <div class="bg-gray-800 rounded-2xl border border-gray-700 p-6 md:p-8 shadow-xl">
        <div class="flex flex-col md:flex-row gap-8">

            {{-- Kolom Kiri: Cover --}}
            <div class="w-full md:w-1/3 lg:w-1/4 flex-shrink-0">
                <div class="aspect-[3/4] overflow-hidden rounded-lg shadow-lg border border-gray-600 relative group">
                    <img src="{{ $this->getCoverUrl($this->book->gambar_cover) }}"
                        alt="{{ $this->book->judul }}"
                        class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105">

                    {{-- Badge Kategori --}}
                    <div class="absolute top-2 right-2 bg-black/60 backdrop-blur-sm text-white text-xs px-2 py-1 rounded-full border border-white/10">
                        {{ $this->book->category->nama_kategori }}
                    </div>
                </div>
            </div>

            {{-- Kolom Kanan: Info --}}
            <div class="flex-1 flex flex-col">
                <h1 class="text-3xl md:text-4xl font-bold text-white mb-2">{{ $this->book->judul }}</h1>
                <p class="text-lg text-gray-400 mb-6">Oleh: <span class="text-indigo-400 font-medium">{{ $this->book->penulis }}</span></p>

                {{-- Statistik Kecil --}}
                <div class="flex gap-6 mb-6 border-b border-gray-700 pb-6">
                    <div>
                        <span class="block text-xs text-gray-500 uppercase tracking-wider">Rating</span>
                        <div class="flex items-center gap-1 text-yellow-400 font-bold text-lg">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.87 5.766a1 1 0 00.95.69h6.05c.969 0 1.372 1.24.588 1.81l-4.89 3.55a1 1 0 00-.364 1.118l1.87 5.766c.3.921-.755 1.688-1.54 1.118l-4.89-3.55a1 1 0 00-1.176 0l-4.89 3.55c-.784.57-1.838-.197-1.54-1.118l1.87-5.766a1 1 0 00-.364-1.118L.587 11.193c-.784-.57-.38-1.81.588-1.81h6.05a1 1 0 00.95-.69L9.049 2.927z"/></svg>
                            {{ number_format($this->book->reviews->avg('rating'), 1) }} <span class="text-xs text-gray-500 font-normal">/ 5</span>
                        </div>
                    </div>

                    {{-- BLOK KODE 1: Stok Tersedia (Telah diimplementasikan) --}}
                    <div>
                        <span class="block text-xs text-gray-500 uppercase tracking-wider">Stok Tersedia</span>
                        {{-- Menggunakan Computed Property $this->stokTersedia yang telah ditambahkan di atas --}}
                        <span class="text-white font-bold text-lg {{ $this->stokTersedia <= 0 ? 'text-red-500' : '' }}">
                            {{ $this->stokTersedia }}
                            <span class="text-xs text-gray-500 font-normal">/ {{ $this->book->lisensi }} copy</span>
                        </span>
                    </div>

                    {{-- Menghapus duplikasi Tombol Aksi yang ada di sini di kode asli --}}
                </div>

                {{-- Sinopsis --}}
                <div class="mb-8">
                    <h3 class="text-lg font-semibold text-white mb-2">Sinopsis</h3>
                    <p class="text-gray-300 leading-relaxed">
                        {{ $this->book->deskripsi ?? 'Belum ada deskripsi untuk buku ini.' }}
                    </p>
                </div>

                {{-- BLOK KODE 2: Tombol Aksi (Telah diimplementasikan) --}}
                <div class="mt-auto">
                    @if($this->stokTersedia <= 0)
                        <button disabled class="w-full md:w-auto bg-gray-500/20 text-gray-400 border border-gray-500/50 px-6 py-3 rounded-lg font-semibold cursor-not-allowed flex items-center justify-center gap-2">
                            Stok Habis
                        </button>
                    @elseif($this->currentStatus === 'pending')
                        <button disabled class="w-full md:w-auto bg-yellow-500/20 text-yellow-400 border border-yellow-500/50 px-6 py-3 rounded-lg font-semibold cursor-not-allowed flex items-center justify-center gap-2">
                            <svg class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            Menunggu Persetujuan
                        </button>
                    @elseif($this->currentStatus === 'dipinjam')
                        <a href="{{ route('user.rak') }}" class="w-full md:w-auto bg-emerald-600 hover:bg-emerald-500 text-white px-6 py-3 rounded-lg font-semibold transition-all flex items-center justify-center gap-2 shadow-lg shadow-emerald-900/20">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                            Buka E-Book
                        </a>
                    @else
                        <button wire:click="pinjamBuku"
                                wire:confirm="Apakah Anda yakin ingin meminjam buku ini?"
                                class="w-full md:w-auto bg-indigo-600 hover:bg-indigo-500 text-white px-6 py-3 rounded-lg font-semibold transition-all flex items-center justify-center gap-2 shadow-lg shadow-indigo-900/20">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            Pinjam Buku
                        </button>
                    @endif
                </div>

                {{-- Menghapus Tombol Aksi yang ada di bawah Sinopsis di kode asli (digantikan oleh BLOK KODE 2) --}}
            </div>
        </div>
    </div>

    {{-- Section Bawah: Ulasan & Rekomendasi --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

        {{-- List Review --}}
        <div class="lg:col-span-2 bg-gray-800 rounded-2xl border border-gray-700 p-6">
            <h3 class="text-xl font-bold text-white mb-6">Ulasan Pembaca ({{ $this->reviews->count() }})</h3>

            <div class="space-y-6">
                @forelse($this->reviews as $review)
                    <div class="flex gap-4 pb-6 border-b border-gray-700 last:border-0 last:pb-0">
                        <div class="w-10 h-10 rounded-full bg-indigo-500 flex items-center justify-center text-white font-bold flex-shrink-0">
                            {{ substr($review->user->name, 0, 1) }}
                        </div>
                        <div>
                            <div class="flex items-center gap-2 mb-1">
                                <span class="font-semibold text-white">{{ $review->user->name }}</span>
                                <span class="text-xs text-gray-500">• {{ $review->created_at->diffForHumans() }}</span>
                            </div>
                            <div class="flex text-yellow-400 text-xs mb-2">
                                @for($i=1; $i<=5; $i++)
                                    <svg class="w-4 h-4 {{ $i <= $review->rating ? 'fill-current' : 'text-gray-600 fill-current' }}" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.87 5.766a1 1 0 00.95.69h6.05c.969 0 1.372 1.24.588 1.81l-4.89 3.55a1 1 0 00-.364 1.118l1.87 5.766c.3.921-.755 1.688-1.54 1.118l-4.89-3.55a1 1 0 00-1.176 0l-4.89 3.55c-.784.57-1.838-.197-1.54-1.118l1.87-5.766a1 1 0 00-.364-1.118L.587 11.193c-.784-.57-.38-1.81.588-1.81h6.05a1 1 0 00.95-.69L9.049 2.927z"/></svg>
                                @endfor
                            </div>
                            <p class="text-gray-300 text-sm leading-relaxed">{{ $review->ulasan }}</p>
                        </div>
                    </div>
                @empty
                    <p class="text-gray-500 italic">Belum ada ulasan untuk buku ini.</p>
                @endforelse
            </div>
        </div>

        {{-- Rekomendasi Buku --}}
        <div>
            <h3 class="text-xl font-bold text-white mb-4">Buku Sejenis</h3>
            <div class="grid grid-cols-1 gap-4">
                @forelse($this->relatedBooks as $related)
                    <a href="{{ route('user.buku.detail', $related->slug) }}" class="flex gap-3 group">
                         <div class="w-16 aspect-[3/4] overflow-hidden rounded bg-gray-700">
                             <img src="{{ $this->getCoverUrl($related->gambar_cover) }}" class="w-full h-full object-cover group-hover:scale-110 transition-transform">
                         </div>
                         <div>
                             <h4 class="text-gray-200 font-medium text-sm line-clamp-2 group-hover:text-indigo-400 transition-colors">{{ $related->judul }}</h4>
                             <span class="text-xs text-gray-500">{{ $related->penulis }}</span>
                         </div>
                    </a>
                @empty
                    <p class="text-gray-500 text-sm">Tidak ada rekomendasi lain saat ini.</p>
                @endforelse
            </div>
        </div>

    </div>
</div>
