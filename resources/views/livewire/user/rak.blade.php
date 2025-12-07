<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\Peminjaman;
use App\Models\Review;
use Carbon\Carbon;

new #[Layout('components.layouts.user')]
class extends Component {

    // State untuk Tab yang aktif.
    #[Url]
    public $tab = 'active';

    /**
     * Cek dan kembalikan buku yang sudah melewati tenggat waktu
     */
    public function checkAndReturnOverdueBooks()
    {
        $overdueLoans = Peminjaman::where('user_id', Auth::id())
            ->where('status', 'dipinjam')
            ->whereNotNull('tanggal_harus_kembali')
            ->whereDate('tanggal_harus_kembali', '<', now())
            ->get();

        $returnedCount = 0;
        foreach ($overdueLoans as $loan) {
            $loan->update(['status' => 'selesai']);
            $returnedCount++;
        }

        if ($returnedCount > 0) {
            session()->flash('message', "Berhasil mengembalikan {$returnedCount} buku yang sudah melewati tenggat waktu.");
        }

        return $returnedCount;
    }

    /**
     * Tab 1: Buku yang sedang dipinjam atau Pending approval
     */
    #[Computed]
    public function activeLoans()
    {
        // Auto-return overdue books setiap kali tab active dibuka
        $this->checkAndReturnOverdueBooks();

        return Peminjaman::with(['book.categories'])
            ->where('user_id', Auth::id())
            ->whereIn('status', ['pending', 'dipinjam'])
            ->orderByDesc('created_at')
            ->get();
    }

    /**
     * Tab 2: Riwayat peminjaman (Selesai / Ditolak)
     */
    #[Computed]
    public function historyLoans()
    {
        return Peminjaman::with(['book.categories'])
            ->where('user_id', Auth::id())
            ->whereIn('status', ['selesai', 'ditolak'])
            ->orderByDesc('updated_at')
            ->get();
    }

    /**
     * Tab 3: Ulasan yang pernah dibuat user
     */
    #[Computed]
    public function myReviews()
    {
        return Review::with(['book'])
            ->where('user_id', Auth::id())
            ->orderByDesc('created_at')
            ->get();
    }

    // Helper Render Warna Status
    public function getStatusColor($status)
    {
        return match ($status) {
            'pending' => 'bg-yellow-500/10 text-yellow-400 ring-yellow-500/20',
            'dipinjam' => 'bg-emerald-500/10 text-emerald-400 ring-emerald-500/20',
            'selesai' => 'bg-blue-500/10 text-blue-400 ring-blue-500/20',
            'ditolak' => 'bg-red-500/10 text-red-400 ring-red-500/20',
            default => 'bg-gray-500/10 text-gray-400 ring-gray-500/20',
        };
    }

    public function getCoverUrl($gambarCover)
    {
        return $gambarCover ? Storage::url($gambarCover) : 'https://placehold.co/300x400/34495e/ffffff?text=No+Cover';
    }
}; ?>

<div class="min-h-screen bg-gray-950 text-gray-200 font-sans selection:bg-indigo-500 selection:text-white">

    <div class="mx-auto max-w-6xl px-4 py-10 sm:px-6 lg:px-8">

        {{-- Flash Message untuk Auto Return --}}
        @if (session()->has('message'))
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)"
                 class="mb-6 rounded-lg bg-emerald-500/10 border border-emerald-500/20 p-4 text-emerald-400">
                <div class="flex items-center gap-3">
                    <svg class="h-5 w-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <p class="text-sm font-medium">{{ session('message') }}</p>
                </div>
            </div>
        @endif

        {{-- Header Section: Clean & Minimalist --}}
        <div class="flex flex-col gap-6 md:flex-row md:items-end md:justify-between mb-10">
            <div>
                <h1 class="text-4xl font-black text-white tracking-tight">Rak Buku Saya</h1>
                <p class="mt-2 text-gray-400">
                    Kelola aktivitas membaca dan peminjaman Anda dalam satu tempat.
                </p>
            </div>
        </div>

        {{-- Navigation: Segmented Control Style --}}
        <div class="mb-8 overflow-x-auto pb-2">
            <nav class="inline-flex rounded-xl bg-gray-900/80 p-1.5 shadow-inner border border-white/5">
                {{-- Active Tab --}}
                <button wire:click="$set('tab', 'active')"
                    class="{{ $tab === 'active' ? 'bg-gray-800 text-white shadow-md ring-1 ring-white/10' : 'text-gray-400 hover:text-gray-200 hover:bg-white/5' }}
                           relative flex items-center gap-2 rounded-lg px-5 py-2.5 text-sm font-semibold transition-all duration-200 whitespace-nowrap">
                    <svg class="w-4 h-4 opacity-70" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                    Sedang Dipinjam
                    @if($this->activeLoans->count() > 0)
                        <span class="ml-1 rounded-md bg-indigo-500/20 px-1.5 py-0.5 text-[10px] font-bold text-indigo-300 border border-indigo-500/20">
                            {{ $this->activeLoans->count() }}
                        </span>
                    @endif
                </button>

                {{-- History Tab --}}
                <button wire:click="$set('tab', 'history')"
                    class="{{ $tab === 'history' ? 'bg-gray-800 text-white shadow-md ring-1 ring-white/10' : 'text-gray-400 hover:text-gray-200 hover:bg-white/5' }}
                           relative flex items-center gap-2 rounded-lg px-5 py-2.5 text-sm font-semibold transition-all duration-200 whitespace-nowrap">
                    <svg class="w-4 h-4 opacity-70" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Riwayat
                </button>

                {{-- Reviews Tab --}}
                <button wire:click="$set('tab', 'reviews')"
                    class="{{ $tab === 'reviews' ? 'bg-gray-800 text-white shadow-md ring-1 ring-white/10' : 'text-gray-400 hover:text-gray-200 hover:bg-white/5' }}
                           relative flex items-center gap-2 rounded-lg px-5 py-2.5 text-sm font-semibold transition-all duration-200 whitespace-nowrap">
                    <svg class="w-4 h-4 opacity-70" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                    </svg>
                    Ulasan Saya
                </button>
            </nav>
        </div>

        {{-- Content Area --}}
        <div class="animate-fade-in-up min-h-[500px]">

            {{-- TAB 1: ACTIVE LOANS (Horizontal Dashboard Cards) --}}
            @if($tab === 'active')
                <div class="flex flex-col gap-4">
                    @forelse($this->activeLoans as $loan)
                        <div class="group relative flex flex-col sm:flex-row overflow-hidden rounded-2xl bg-gray-900/40 border border-white/5 hover:bg-gray-900/80 hover:border-gray-700 transition-all duration-300">

                            {{-- Side Accent Status Bar --}}
                            <div class="absolute left-0 top-0 bottom-0 w-1 {{ $this->getStatusColor($loan->status) == 'bg-green-100 text-green-800' ? 'bg-emerald-500' : ($this->getStatusColor($loan->status) == 'bg-yellow-100 text-yellow-800' ? 'bg-amber-500' : 'bg-indigo-500') }}"></div>

                            {{-- Image --}}
                            <div class="sm:w-36 md:w-48 shrink-0 relative bg-gray-800">
                                <img src="{{ $this->getCoverUrl($loan->book->gambar_cover) }}"
                                     alt="{{ $loan->book->judul }}"
                                     class="h-64 sm:h-full w-full object-cover opacity-90 transition-opacity group-hover:opacity-100">
                            </div>

                            {{-- Content --}}
                            <div class="flex-1 p-5 sm:p-6 flex flex-col justify-between">
                                <div>
                                    <div class="flex items-start justify-between">
                                        <div>
                                            <h3 class="text-xl font-bold text-white group-hover:text-indigo-400 transition-colors">
                                                {{ $loan->book->judul }}
                                            </h3>
                                            <p class="text-sm text-gray-400 mt-1">{{ $loan->book->penulis }}</p>
                                        </div>
                                        <div class="hidden sm:block">
                                             <span class="inline-flex items-center rounded-full bg-gray-800 px-3 py-1 text-xs font-medium text-gray-300 ring-1 ring-inset ring-gray-700">
                                                {{ ucfirst($loan->status) }}
                                            </span>
                                        </div>
                                    </div>

                                    <div class="mt-6 grid grid-cols-2 gap-4 border-t border-gray-800/50 pt-4">
                                        <div>
                                            <span class="text-xs uppercase tracking-wider text-gray-500 font-bold">Dipinjam</span>
                                            <p class="mt-1 font-mono text-sm text-gray-300">{{ $loan->tanggal_pinjam ? $loan->tanggal_pinjam->format('d M Y') : '-' }}</p>
                                        </div>
                                        <div>
                                            <span class="text-xs uppercase tracking-wider text-gray-500 font-bold">Tenggat</span>
                                            <p class="mt-1 font-mono text-sm {{ $loan->status == 'dipinjam' && now()->gt($loan->tanggal_harus_kembali) ? 'text-red-400 font-bold' : 'text-indigo-300' }}">
                                                {{ $loan->tanggal_harus_kembali ? $loan->tanggal_harus_kembali->format('d M Y') : '-' }}
                                            </p>
                                            @if($loan->status == 'dipinjam' && $loan->tanggal_harus_kembali && now()->gt($loan->tanggal_harus_kembali))
                                                <p class="mt-1 text-xs text-red-400 flex items-center gap-1">
                                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                                    </svg>
                                                    Terlambat {{ now()->diffInDays($loan->tanggal_harus_kembali) }} hari
                                                </p>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-6 flex items-center justify-end gap-3">
                                    @if($loan->status == 'dipinjam')
                                        <a href="{{ route('user.baca', $loan->book->slug) }}"
                                           class="inline-flex w-full sm:w-auto items-center justify-center gap-2 rounded-lg bg-indigo-600 px-6 py-2.5 text-sm font-semibold text-white shadow-lg shadow-indigo-500/20 transition-all hover:bg-indigo-500 hover:scale-[1.02]">
                                            <span>Baca Buku</span>
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                                        </a>
                                    @else
                                        <button disabled class="inline-flex w-full sm:w-auto items-center justify-center rounded-lg bg-gray-800 border border-gray-700 px-6 py-2.5 text-sm font-medium text-gray-400 cursor-not-allowed">
                                            Menunggu Persetujuan
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="flex flex-col items-center justify-center rounded-2xl border border-dashed border-gray-800 bg-gray-900/30 py-20 text-center">
                            <div class="h-16 w-16 mb-4 rounded-full bg-gray-800/50 flex items-center justify-center text-gray-500">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                            </div>
                            <h3 class="text-lg font-medium text-white">Tidak ada pinjaman aktif</h3>
                            <a href="{{ route('user.koleksi') }}" class="mt-4 text-indigo-400 hover:text-indigo-300 hover:underline">Jelajahi buku baru &rarr;</a>
                        </div>
                    @endforelse
                </div>
            @endif

            {{-- TAB 2: HISTORY LOANS (Clean Modern List) --}}
            @if($tab === 'history')
                <div class="rounded-xl border border-gray-800 bg-gray-900/50 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm text-gray-400">
                            <thead class="bg-gray-900 text-xs uppercase font-semibold text-gray-500">
                                <tr>
                                    <th scope="col" class="px-6 py-4">Informasi Buku</th>
                                    <th scope="col" class="px-6 py-4 whitespace-nowrap">Tanggal Pinjam</th>
                                    <th scope="col" class="px-6 py-4 whitespace-nowrap">Tanggal Kembali</th>
                                    <th scope="col" class="px-6 py-4 text-right">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-800">
                                @forelse($this->historyLoans as $history)
                                    <tr class="hover:bg-gray-800/50 transition-colors">
                                        <td class="px-6 py-4">
                                            <div class="flex items-center gap-4">
                                                <img class="h-12 w-9 rounded object-cover ring-1 ring-white/10" src="{{ $this->getCoverUrl($history->book->gambar_cover) }}" alt="">
                                                <div>
                                                    <div class="font-medium text-white">{{ $history->book->judul }}</div>
                                                    <div class="text-xs text-gray-500">{{ Str::limit($history->book->penulis, 20) }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap font-mono text-gray-300">
                                            {{ $history->tanggal_pinjam ? $history->tanggal_pinjam->format('d/m/Y') : '-' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap font-mono text-gray-300">
                                            {{ $history->tanggal_harus_kembali ? $history->tanggal_harus_kembali->format('d/m/Y') : '-' }}
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            <span class="inline-block rounded px-2 py-1 text-xs font-medium ring-1 ring-inset {{ $this->getStatusColor($history->status) }}">
                                                {{ ucfirst($history->status) }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-6 py-12 text-center text-gray-500 italic">
                                            Belum ada riwayat peminjaman tercatat.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            {{-- TAB 3: REVIEWS (Grid Cards) --}}
            @if($tab === 'reviews')
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    @forelse($this->myReviews as $review)
                        <div class="flex flex-col bg-gray-900 border border-gray-800 rounded-xl p-6 hover:border-gray-700 hover:shadow-lg transition-all">
                            <div class="flex items-center justify-between mb-4">
                                <div class="flex items-center gap-3">
                                    <img src="{{ $this->getCoverUrl($review->book->gambar_cover) }}" class="h-10 w-10 rounded object-cover ring-1 ring-white/10">
                                    <div>
                                        <h4 class="text-sm font-bold text-gray-200 line-clamp-1">{{ $review->book->judul }}</h4>
                                        <div class="flex items-center text-yellow-500">
                                            @for($i=0; $i<5; $i++)
                                                <svg class="w-3 h-3 {{ $i < $review->rating ? 'fill-current' : 'text-gray-700' }}" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                            @endfor
                                        </div>
                                    </div>
                                </div>
                                <span class="text-xs text-gray-600">{{ $review->created_at->diffForHumans() }}</span>
                            </div>

                            <div class="relative bg-gray-950/50 p-4 rounded-lg flex-1">
                                <p class="text-gray-400 text-sm pl-4 relative z-10 italic">"{{ $review->ulasan }}"</p>
                            </div>
                        </div>
                    @empty
                        <div class="col-span-full py-12 text-center text-gray-500 bg-gray-900/30 rounded-xl border border-dashed border-gray-800">
                            Belum ada ulasan yang Anda buat.
                        </div>
                    @endforelse
                </div>
            @endif

        </div>
    </div>
</div>
