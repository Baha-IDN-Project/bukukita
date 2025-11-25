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

    public Book $book;

    // --- PROPERTI REVIEW ---
    #[Validate('required|integer|min:1|max:5', message: 'Harap pilih bintang 1 sampai 5.')]
    public $rating = 0;

    #[Validate('nullable|string|max:1000', message: 'Ulasan maksimal 1000 karakter.')]
    public $ulasan = '';

    public $showReviewModal = false;

    public function mount()
    {
        $this->book->load(['categories', 'reviews.user']); // Eager load

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
        // Refresh data reviews
        unset($this->reviews);
        $this->dispatch('notify', message: 'Ulasan berhasil disimpan!');
    }

    #[Computed]
    public function reviews()
    {
        return $this->book->reviews()->with('user')->latest()->get();
    }

    #[Computed]
    public function relatedBooks()
    {
        $categoryIds = $this->book->categories->pluck('id');

        if ($categoryIds->isEmpty()) {
            return collect();
        }

        return Book::whereHas('categories', function ($query) use ($categoryIds) {
                $query->whereIn('categories.id', $categoryIds);
            })
            ->where('id', '!=', $this->book->id)
            ->take(5) // Ambil 5 untuk grid
            ->get();
    }

    #[Computed]
    public function currentStatus()
    {
        if (!Auth::check()) return null;

        $loan = Peminjaman::where('user_id', Auth::id())
            ->where('book_id', $this->book->id)
            ->whereIn('status', ['pending', 'dipinjam'])
            ->first();

        return $loan ? $loan->status : null;
    }

    #[Computed]
    public function stokTersedia()
    {
        return max(0, $this->book->lisensi - $this->book->jumlah_dipinjam);
    }

    public function pinjamBuku()
    {
        if (!Auth::check()) {
            return $this->redirect(route('login'));
        }

        if ($this->stokTersedia <= 0) {
            $this->dispatch('notify-error', message: 'Stok buku habis.');
            return;
        }

        if ($this->currentStatus) {
             $this->dispatch('notify-error', message: 'Anda sudah meminjam buku ini.');
            return;
        }

        Peminjaman::create([
            'user_id' => Auth::id(),
            'book_id' => $this->book->id,
            'status' => 'pending',
        ]);

        $this->dispatch('notify', message: 'Permintaan peminjaman dikirim.');
        $this->redirect(route('user.rak'), navigate: true);
    }

    public function getCoverUrl($gambarCover)
    {
        return $gambarCover ? Storage::url($gambarCover) : 'https://placehold.co/400x600/1e293b/cbd5e1?text=No+Cover';
    }
}; ?>

<div class="min-h-screen bg-gray-950 pb-20 text-gray-100">

    {{-- 1. HERO BACKDROP (Visual Aesthetic) --}}
    <div class="relative h-[300px] w-full overflow-hidden">
        {{-- Background Image Blurred --}}
        <div class="absolute inset-0 bg-cover bg-center opacity-30 blur-xl filter"
             style="background-image: url('{{ $this->getCoverUrl($this->book->gambar_cover) }}');">
        </div>
        {{-- Gradient Overlay --}}
        <div class="absolute inset-0 bg-gradient-to-b from-gray-950/50 via-gray-950/80 to-gray-950"></div>

        {{-- Breadcrumb (On top of hero) --}}
        <div class="relative z-10 mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
            <nav class="flex text-sm font-medium text-gray-400">
                <a href="{{ route('user.koleksi') }}" class="hover:text-white transition-colors">Koleksi</a>
                <span class="mx-3 text-gray-600">/</span>
                <span class="truncate text-indigo-400">{{ $this->book->judul }}</span>
            </nav>
        </div>
    </div>

    {{-- 2. MAIN CONTENT --}}
    <div class="relative z-20 mx-auto -mt-40 grid max-w-7xl grid-cols-1 gap-8 px-4 sm:px-6 lg:grid-cols-12 lg:px-8">

        {{-- LEFT COLUMN: COVER & ACTIONS (Sticky on Desktop) --}}
        <div class="lg:col-span-4 lg:row-span-2">
            <div class="sticky top-8 flex flex-col gap-6">
                {{-- Book Cover --}}
                <div class="group relative aspect-[2/3] w-full overflow-hidden rounded-xl bg-gray-800 shadow-2xl ring-1 ring-white/10">
                    <img src="{{ $this->getCoverUrl($this->book->gambar_cover) }}"
                         alt="{{ $this->book->judul }}"
                         class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105">

                    {{-- Status Badge Overlay --}}
                    @if($this->stokTersedia <= 0)
                        <div class="absolute inset-0 flex items-center justify-center bg-black/70 backdrop-blur-sm">
                            <span class="rotate-12 rounded-lg border-2 border-red-500 px-4 py-2 text-xl font-bold uppercase tracking-widest text-red-500">
                                Stok Habis
                            </span>
                        </div>
                    @endif
                </div>

                {{-- Action Buttons --}}
                <div class="rounded-xl border border-gray-800 bg-gray-900 p-4 shadow-lg">
                    @if($this->stokTersedia <= 0)
                        <button disabled class="flex w-full cursor-not-allowed items-center justify-center gap-2 rounded-lg bg-gray-800 py-3.5 font-semibold text-gray-500">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
                            Stok Tidak Tersedia
                        </button>

                    @elseif($this->currentStatus === 'pending')
                        <button disabled class="flex w-full cursor-not-allowed items-center justify-center gap-2 rounded-lg bg-yellow-500/10 py-3.5 font-semibold text-yellow-500 ring-1 ring-inset ring-yellow-500/20">
                            <svg class="h-5 w-5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            Menunggu Persetujuan
                        </button>

                    @elseif($this->currentStatus === 'dipinjam')
                        <div class="flex flex-col gap-3">
                            <a href="{{ route('user.baca', $this->book->slug) }}" class="flex w-full items-center justify-center gap-2 rounded-lg bg-emerald-600 py-3.5 font-semibold text-white shadow-lg shadow-emerald-900/20 transition hover:bg-emerald-500 hover:-translate-y-0.5">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                                Baca Sekarang
                            </a>
                            <button wire:click="openReviewModal" class="flex w-full items-center justify-center gap-2 rounded-lg border border-gray-700 bg-gray-800 py-3 font-medium text-gray-300 transition hover:bg-gray-700 hover:text-white">
                                <svg class="h-5 w-5 text-yellow-500" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" /></svg>
                                Beri Ulasan
                            </button>
                        </div>

                    @else
                        <button wire:click="pinjamBuku"
                                wire:confirm="Apakah Anda yakin ingin meminjam buku ini?"
                                class="flex w-full items-center justify-center gap-2 rounded-lg bg-indigo-600 py-3.5 font-bold text-white shadow-lg shadow-indigo-900/30 transition hover:bg-indigo-500 hover:shadow-indigo-500/20 hover:-translate-y-0.5">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                            Pinjam Buku
                        </button>
                        <p class="mt-3 text-center text-xs text-gray-500">
                            Stok tersedia: <span class="text-gray-300 font-bold">{{ $this->stokTersedia }}</span> dari {{ $this->book->lisensi }}
                        </p>
                    @endif
                </div>
            </div>
        </div>

        {{-- RIGHT COLUMN: DETAILS, REVIEWS, RELATED --}}
        <div class="lg:col-span-8">

            {{-- Header Info --}}
            <div class="mb-8">
                {{-- Categories --}}
                <div class="mb-4 flex flex-wrap gap-2">
                    @foreach($this->book->categories as $category)
                        <span class="inline-flex items-center rounded-md bg-indigo-400/10 px-2 py-1 text-xs font-medium text-indigo-400 ring-1 ring-inset ring-indigo-400/20">
                            {{ $category->nama_kategori }}
                        </span>
                    @endforeach
                </div>

                <h1 class="text-3xl font-extrabold tracking-tight text-white sm:text-4xl lg:text-5xl">
                    {{ $this->book->judul }}
                </h1>

                <div class="mt-4 flex items-center gap-2 text-lg text-gray-300">
                    <span>Oleh</span>
                    <span class="font-semibold text-white">{{ $this->book->penulis }}</span>
                </div>
            </div>

            {{-- Stats Grid --}}
            <div class="mb-8 grid grid-cols-2 gap-4 rounded-xl border border-gray-800 bg-gray-900/50 p-4 sm:grid-cols-3">
                <div class="border-r border-gray-800 p-2 text-center last:border-0">
                    <p class="text-xs uppercase tracking-wider text-gray-500">Rating</p>
                    <div class="mt-1 flex items-center justify-center gap-1 text-xl font-bold text-white">
                        <svg class="h-5 w-5 text-yellow-500" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1-81h3.461a1 1 0 00.951-.69l1.07-3.292z" /></svg>
                        {{ number_format($this->book->reviews->avg('rating'), 1) }}
                    </div>
                </div>
                <div class="border-r border-gray-800 p-2 text-center last:border-0">
                    <p class="text-xs uppercase tracking-wider text-gray-500">Ulasan</p>
                    <p class="mt-1 text-xl font-bold text-white">{{ $this->book->reviews->count() }}</p>
                </div>
                <div class="p-2 text-center">
                    <p class="text-xs uppercase tracking-wider text-gray-500">Dipinjam</p>
                    <p class="mt-1 text-xl font-bold text-white">{{ $this->book->jumlah_dipinjam }}</p>
                </div>
            </div>

            {{-- Synopsis --}}
            <div class="mb-12">
                <h3 class="mb-4 text-xl font-bold text-white">Sinopsis</h3>
                <div class="prose prose-invert max-w-none text-gray-300 leading-relaxed">
                    <p>{{ $this->book->deskripsi ?? 'Belum ada deskripsi yang tersedia untuk buku ini.' }}</p>
                </div>
            </div>

            <hr class="mb-12 border-gray-800">

            {{-- Reviews Section --}}
            <div class="mb-12">
                <div class="mb-6 flex items-center justify-between">
                    <h3 class="text-2xl font-bold text-white">Ulasan Pembaca</h3>
                </div>

                <div class="space-y-6">
                    @forelse($this->reviews as $review)
                        <div class="flex gap-4 rounded-xl border border-gray-800 bg-gray-900/30 p-4 transition hover:bg-gray-900/60">
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 font-bold text-white">
                                {{ substr($review->user->name, 0, 1) }}
                            </div>
                            <div class="flex-1">
                                <div class="mb-1 flex items-center justify-between">
                                    <h4 class="font-bold text-gray-200">{{ $review->user->name }}</h4>
                                    <span class="text-xs text-gray-500">{{ $review->created_at->diffForHumans() }}</span>
                                </div>
                                <div class="mb-2 flex text-yellow-500">
                                    @for($i=1; $i<=5; $i++)
                                        <svg class="h-4 w-4 {{ $i <= $review->rating ? 'fill-current' : 'text-gray-700 fill-current' }}" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" /></svg>
                                    @endfor
                                </div>
                                <p class="text-sm leading-relaxed text-gray-400">
                                    {{ $review->ulasan }}
                                </p>
                            </div>
                        </div>
                    @empty
                        <div class="rounded-xl border border-dashed border-gray-700 p-8 text-center text-gray-500">
                            Belum ada ulasan. Jadilah yang pertama memberikan ulasan!
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- Related Books --}}
            @if($this->relatedBooks->isNotEmpty())
                <div>
                    <h3 class="mb-6 text-2xl font-bold text-white">Buku Serupa</h3>
                    <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5">
                        @foreach($this->relatedBooks as $related)
                            <a href="{{ route('user.buku.detail', $related->slug) }}" class="group relative block overflow-hidden rounded-lg bg-gray-900 shadow-md transition-all hover:-translate-y-1 hover:shadow-xl">
                                <div class="aspect-[2/3] w-full overflow-hidden">
                                    <img src="{{ $this->getCoverUrl($related->gambar_cover) }}" alt="{{ $related->judul }}" class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-110">
                                </div>
                                <div class="p-3">
                                    <h4 class="line-clamp-2 text-sm font-bold text-gray-200 group-hover:text-indigo-400">{{ $related->judul }}</h4>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- 3. REVIEW MODAL --}}
    @if($showReviewModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-black/80 p-4 backdrop-blur-sm"
         x-data="{ rating: @entangle('rating') }">

        <div class="relative w-full max-w-lg rounded-2xl border border-gray-700 bg-gray-900 p-6 shadow-2xl ring-1 ring-white/10"
             @click.away="$wire.set('showReviewModal', false)">

            <div class="mb-6 flex items-center justify-between">
                <h3 class="text-xl font-bold text-white">Bagikan Pendapat Anda</h3>
                <button wire:click="$set('showReviewModal', false)" class="text-gray-400 hover:text-white">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>

            <form wire:submit="simpanUlasan">
                {{-- Star Rating Input --}}
                <div class="mb-6 flex flex-col items-center justify-center gap-2">
                    <label class="text-sm font-medium text-gray-400">Beri Rating</label>
                    <div class="flex gap-1" x-data="{ hoverRating: 0 }" @mouseleave="hoverRating = 0">
                        @for($i=1; $i<=5; $i++)
                            <button type="button"
                                    @click="rating = {{ $i }}"
                                    @mouseenter="hoverRating = {{ $i }}"
                                    class="transition-transform hover:scale-110 focus:outline-none">
                                <svg class="h-10 w-10 transition-colors"
                                     :class="(hoverRating >= {{ $i }} || (hoverRating === 0 && rating >= {{ $i }})) ? 'text-yellow-500 fill-current' : 'text-gray-700 fill-current'"
                                     viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                </svg>
                            </button>
                        @endfor
                    </div>
                    @error('rating') <p class="text-sm text-red-500">{{ $message }}</p> @enderror
                </div>

                <div class="mb-6">
                    <label class="mb-2 block text-sm font-medium text-gray-300">Ulasan Tertulis (Opsional)</label>
                    <textarea wire:model="ulasan"
                              rows="4"
                              class="w-full rounded-lg border border-gray-700 bg-gray-800 p-3 text-white placeholder-gray-500 focus:border-indigo-500 focus:ring-indigo-500"
                              placeholder="Ceritakan apa yang Anda suka atau tidak suka dari buku ini..."></textarea>
                    @error('ulasan') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                </div>

                <div class="flex justify-end gap-3">
                    <button type="button" wire:click="$set('showReviewModal', false)" class="rounded-lg px-4 py-2 text-sm font-medium text-gray-300 transition hover:text-white">
                        Batal
                    </button>
                    <button type="submit" class="flex items-center gap-2 rounded-lg bg-indigo-600 px-6 py-2 text-sm font-bold text-white transition hover:bg-indigo-500 disabled:opacity-50">
                        <span wire:loading.remove wire:target="simpanUlasan">Kirim Ulasan</span>
                        <span wire:loading wire:target="simpanUlasan">Menyimpan...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

</div>
