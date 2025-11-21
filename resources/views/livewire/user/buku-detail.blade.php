<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Validate;
use App\Models\Book;
use App\Models\Peminjaman;
use App\Models\Review;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

new #[Layout('components.layouts.user')]
class extends Component {

    // Properti Book otomatis diisi oleh Route Model Binding
    public Book $book;

    // --- PROPERTI REVIEW ---
    #[Validate('required|integer|min:1|max:5', message: 'Harap pilih bintang 1 sampai 5.')]
    public $rating = 0;

    #[Validate('nullable|string|max:1000', message: 'Ulasan maksimal 1000 karakter.')]
    public $ulasan = '';

    public $showReviewModal = false;
    // -----------------------

    public function mount()
    {
        // Cek apakah user sudah pernah review buku ini saat halaman dimuat
        if (Auth::check()) {
            $existingReview = Review::where('user_id', Auth::id())
                ->where('book_id', $this->book->id)
                ->first();

            if ($existingReview) {
                $this->rating = $existingReview->rating;
                $this->ulasan = $existingReview->ulasan;
            }
        }
    }

    public function openReviewModal()
    {
        $this->showReviewModal = true;
    }

    public function simpanUlasan()
    {
        $this->validate();

        Review::updateOrCreate(
            [
                'user_id' => Auth::id(),
                'book_id' => $this->book->id
            ],
            [
                'rating' => $this->rating,
                'ulasan' => $this->ulasan
            ]
        );

        $this->showReviewModal = false;
        session()->flash('success', 'Terima kasih! Ulasan Anda berhasil disimpan.');
    }

    #[Computed]
    public function reviews()
    {
        return $this->book->reviews()->with('user')->latest()->get();
    }

    #[Computed]
    public function relatedBooks()
    {
        return Book::where('category_id', $this->book->category_id)
            ->where('id', '!=', $this->book->id)
            ->take(4)
            ->get();
    }

    #[Computed]
    public function currentStatus()
    {
        $loan = Peminjaman::where('user_id', Auth::id())
            ->where('book_id', $this->book->id)
            ->whereIn('status', ['pending', 'dipinjam'])
            ->first();

        if ($loan) {
            return $loan->status;
        }

        return null;
    }

    #[Computed]
    public function stokTersedia()
    {
        return $this->book->lisensi - $this->book->jumlah_dipinjam;
    }

    public function pinjamBuku()
    {
        $stokTersedia = $this->stokTersedia;

        if ($stokTersedia <= 0) {
            session()->flash('error', 'Maaf, stok buku ini sedang habis dipinjam (semua lisensi terpakai).');
            $this->redirect(route('user.koleksi'), navigate: true);
            return;
        }

        if ($this->currentStatus) {
            session()->flash('warning', 'Anda sudah memiliki permintaan peminjaman yang aktif untuk buku ini: ' . ucfirst($this->currentStatus));
            $this->redirect(route('user.rak'), navigate: true);
            return;
        }

        Peminjaman::create([
            'user_id' => Auth::id(),
            'book_id' => $this->book->id,
            'tanggal_pinjam' => null,
            'tanggal_harus_kembali' => null,
            'status' => 'pending',
        ]);

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
    <div class="bg-gray-800 rounded-2xl border border-gray-700 p-6 md:p-8 shadow-xl relative">
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
                        <div class="flex items-center gap-1 text-yellow-500 font-bold text-lg">
                            {{-- SVG STAR UPDATED (Header) --}}
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5">
                                <path fill-rule="evenodd" d="M10.788 3.21c.448-1.077 1.976-1.077 2.424 0l2.082 5.007 5.404.433c1.164.093 1.636 1.545.749 2.305l-4.117 3.527 1.257 5.273c.271 1.136-.964 2.033-1.96 1.425L12 18.354 7.373 21.18c-.996.608-2.231-.29-1.96-1.425l1.257-5.273-4.117-3.527c-.887-.76-.415-2.212.749-2.305l5.404-.433 2.082-5.006z" clip-rule="evenodd" />
                            </svg>
                            {{ number_format($this->book->reviews->avg('rating'), 1) }} <span class="text-xs text-gray-500 font-normal">/ 5</span>
                        </div>
                    </div>

                    {{-- Stok Tersedia --}}
                    <div>
                        <span class="block text-xs text-gray-500 uppercase tracking-wider">Stok Tersedia</span>
                        <span class="text-white font-bold text-lg {{ $this->stokTersedia <= 0 ? 'text-red-500' : '' }}">
                            {{ $this->stokTersedia }}
                            <span class="text-xs text-gray-500 font-normal">/ {{ $this->book->lisensi }} copy</span>
                        </span>
                    </div>
                </div>

                {{-- Sinopsis --}}
                <div class="mb-8">
                    <h3 class="text-lg font-semibold text-white mb-2">Sinopsis</h3>
                    <p class="text-gray-300 leading-relaxed">
                        {{ $this->book->deskripsi ?? 'Belum ada deskripsi untuk buku ini.' }}
                    </p>
                </div>

                {{-- Tombol Aksi --}}
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
                        <div class="flex flex-col md:flex-row gap-3 w-full md:w-auto">
                            {{-- Tombol Baca --}}
                            <a href="{{ route('user.baca', $this->book->slug) }}" class="flex-1 md:flex-none bg-emerald-600 hover:bg-emerald-500 text-white px-6 py-3 rounded-lg font-semibold transition-all flex items-center justify-center gap-2 shadow-lg shadow-emerald-900/20">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                                Buka E-Book
                            </a>

                            {{-- Tombol Review --}}
                            <button wire:click="openReviewModal" class="bg-gray-700 hover:bg-gray-600 text-white px-4 py-3 rounded-lg transition-colors border border-gray-600 flex items-center justify-center gap-2" title="Beri Ulasan">
                                {{-- SVG STAR UPDATED (Button) --}}
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5 text-yellow-500">
                                    <path fill-rule="evenodd" d="M10.788 3.21c.448-1.077 1.976-1.077 2.424 0l2.082 5.007 5.404.433c1.164.093 1.636 1.545.749 2.305l-4.117 3.527 1.257 5.273c.271 1.136-.964 2.033-1.96 1.425L12 18.354 7.373 21.18c-.996.608-2.231-.29-1.96-1.425l1.257-5.273-4.117-3.527c-.887-.76-.415-2.212.749-2.305l5.404-.433 2.082-5.006z" clip-rule="evenodd" />
                                </svg>
                                <span>Beri Nilai</span>
                            </button>
                        </div>

                    @else
                        <button wire:click="pinjamBuku"
                                wire:confirm="Apakah Anda yakin ingin meminjam buku ini?"
                                class="w-full md:w-auto bg-indigo-600 hover:bg-indigo-500 text-white px-6 py-3 rounded-lg font-semibold transition-all flex items-center justify-center gap-2 shadow-lg shadow-indigo-900/20">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            Pinjam Buku
                        </button>
                    @endif
                </div>
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
                            <div class="flex text-yellow-500 text-xs mb-2">
                                @for($i=1; $i<=5; $i++)
                                    {{-- SVG STAR UPDATED (List Review) --}}
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-4 h-4 {{ $i <= $review->rating ? 'text-yellow-500' : 'text-gray-600' }}">
                                        <path fill-rule="evenodd" d="M10.788 3.21c.448-1.077 1.976-1.077 2.424 0l2.082 5.007 5.404.433c1.164.093 1.636 1.545.749 2.305l-4.117 3.527 1.257 5.273c.271 1.136-.964 2.033-1.96 1.425L12 18.354 7.373 21.18c-.996.608-2.231-.29-1.96-1.425l1.257-5.273-4.117-3.527c-.887-.76-.415-2.212.749-2.305l5.404-.433 2.082-5.006z" clip-rule="evenodd" />
                                    </svg>
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

    {{-- MODAL REVIEW --}}
    @if($showReviewModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm"
             x-data="{ rating: @entangle('rating') }">

            <div class="bg-gray-800 rounded-2xl w-full max-w-md border border-gray-700 shadow-2xl p-6 relative">

                {{-- Header Modal --}}
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-xl font-bold text-white">Beri Ulasan</h3>
                    <button wire:click="$set('showReviewModal', false)" class="text-gray-400 hover:text-white">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>

                {{-- Form --}}
                <form wire:submit="simpanUlasan">

                {{-- LOGIC BINTANG RESPONSIF --}}
                {{-- Kita buat variable localRating & hoverRating di Alpine --}}
                <div class="flex justify-center gap-2 mb-6"
                     x-data="{
                        localRating: $wire.entangle('rating'),
                        hoverRating: 0
                     }"
                     @mouseleave="hoverRating = 0">

                    @for($i=1; $i<=5; $i++)
                        <button type="button"
                                {{-- Saat diklik: set rating lokal (instan) --}}
                                @click="localRating = {{ $i }}"

                                {{-- Saat hover: set rating bayangan --}}
                                @mouseenter="hoverRating = {{ $i }}"

                                class="focus:outline-none transition-transform duration-100 hover:scale-110 p-1">

                            {{-- SVG Bintang --}}
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"
                                 class="w-10 h-10 transition-colors duration-150"
                                 {{-- Logic Warna: Jika sedang di-hover ATAU (tidak hover TAPI rating >= index) --}}
                                 :class="(hoverRating >= {{ $i }} || (hoverRating === 0 && localRating >= {{ $i }}))
                                            ? 'text-yellow-500'
                                            : 'text-gray-600'">
                                <path fill-rule="evenodd" d="M10.788 3.21c.448-1.077 1.976-1.077 2.424 0l2.082 5.007 5.404.433c1.164.093 1.636 1.545.749 2.305l-4.117 3.527 1.257 5.273c.271 1.136-.964 2.033-1.96 1.425L12 18.354 7.373 21.18c-.996.608-2.231-.29-1.96-1.425l1.257-5.273-4.117-3.527c-.887-.76-.415-2.212.749-2.305l5.404-.433 2.082-5.006z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    @endfor
                </div>

                {{-- Pesan Error --}}
                @error('rating')
                    <div class="text-center mb-4">
                        <span class="text-red-500 text-sm font-medium bg-red-500/10 px-3 py-1 rounded">
                            {{ $message }}
                        </span>
                    </div>
                @enderror

                {{-- Text Area --}}
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-300 mb-2">Pendapat Anda</label>
                    <textarea wire:model="ulasan" rows="4" class="w-full bg-gray-900 border border-gray-700 rounded-lg p-3 text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent placeholder-gray-600" placeholder="Bagaimana menurut Anda buku ini?"></textarea>
                    @error('ulasan') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                {{-- Button Submit --}}
                <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-500 text-white font-bold py-3 rounded-lg transition-colors flex justify-center items-center gap-2">
                    <span wire:loading.remove wire:target="simpanUlasan">Kirim Ulasan</span>
                    <span wire:loading wire:target="simpanUlasan">
                        <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    </span>
                </button>
            </form>
            </div>
        </div>
    @endif

</div>
