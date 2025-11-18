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
    // #[Url] membuat tab tetap terpilih meski halaman di-refresh.
    #[Url]
    public $tab = 'active';

    /**
     * Tab 1: Buku yang sedang dipinjam atau Pending approval
     */
    #[Computed]
    public function activeLoans()
    {
        return Peminjaman::with(['book.category'])
            ->where('user_id', Auth::id())
            ->whereIn('status', ['pending', 'dipinjam']) // Ambil yang pending dan dipinjam
            ->orderByDesc('created_at')
            ->get();
    }

    /**
     * Tab 2: Riwayat peminjaman (Selesai / Ditolak)
     */
    #[Computed]
    public function historyLoans()
    {
        return Peminjaman::with(['book.category'])
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
            'pending' => 'bg-yellow-500/20 text-yellow-400 border-yellow-500/50',
            'dipinjam' => 'bg-emerald-500/20 text-emerald-400 border-emerald-500/50',
            'selesai' => 'bg-blue-500/20 text-blue-400 border-blue-500/50',
            'ditolak' => 'bg-red-500/20 text-red-400 border-red-500/50',
            default => 'bg-gray-500/20 text-gray-400 border-gray-500/50',
        };
    }

    public function getCoverUrl($gambarCover)
    {
        return $gambarCover ? Storage::url($gambarCover) : 'https://placehold.co/300x400/34495e/ffffff?text=No+Cover';
    }
}; ?>

<div class="flex h-full w-full flex-col gap-6">

    <div class="flex flex-col gap-2">
        <h1 class="text-2xl font-bold text-white">Rak Peminjaman</h1>
        <p class="text-gray-400 text-sm">Kelola buku yang sedang Anda pinjam dan lihat riwayat bacaan Anda.</p>
    </div>

    <div class="border-b border-gray-700">
        <nav class="-mb-px flex space-x-8" aria-label="Tabs">

            {{-- Tab Active --}}
            <button wire:click="$set('tab', 'active')"
                class="{{ $tab === 'active' ? 'border-indigo-500 text-indigo-400' : 'border-transparent text-gray-400 hover:text-gray-300 hover:border-gray-300' }}
                       whitespace-nowrap border-b-2 py-4 px-1 text-sm font-medium transition-colors">
                Sedang Dipinjam
                @if($this->activeLoans->count() > 0)
                    <span class="ml-2 rounded-full bg-indigo-500/20 px-2.5 py-0.5 text-xs font-medium text-indigo-400">
                        {{ $this->activeLoans->count() }}
                    </span>
                @endif
            </button>

            {{-- Tab History --}}
            <button wire:click="$set('tab', 'history')"
                class="{{ $tab === 'history' ? 'border-indigo-500 text-indigo-400' : 'border-transparent text-gray-400 hover:text-gray-300 hover:border-gray-300' }}
                       whitespace-nowrap border-b-2 py-4 px-1 text-sm font-medium transition-colors">
                Riwayat Peminjaman
            </button>

            {{-- Tab Reviews --}}
            <button wire:click="$set('tab', 'reviews')"
                class="{{ $tab === 'reviews' ? 'border-indigo-500 text-indigo-400' : 'border-transparent text-gray-400 hover:text-gray-300 hover:border-gray-300' }}
                       whitespace-nowrap border-b-2 py-4 px-1 text-sm font-medium transition-colors">
                Ulasan Saya
            </button>
        </nav>
    </div>

    <div class="mt-2 min-h-[400px]">

        @if($tab === 'active')
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @forelse($this->activeLoans as $loan)
                    <div class="bg-gray-800 rounded-xl border border-gray-700 overflow-hidden flex shadow-sm hover:shadow-md transition-shadow">
                        <div class="w-24 sm:w-32 bg-gray-900 shrink-0 relative">
                             <img src="{{ $this->getCoverUrl($loan->book->gambar_cover) }}"
                                 alt="{{ $loan->book->judul }}"
                                 class="h-full w-full object-cover">
                        </div>

                        <div class="p-4 flex flex-col flex-1">
                            <div class="mb-2">
                                <span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset {{ $this->getStatusColor($loan->status) }}">
                                    {{ ucfirst($loan->status) }}
                                </span>
                            </div>

                            <h3 class="text-lg font-semibold text-white line-clamp-2 leading-tight mb-1">
                                {{ $loan->book->judul }}
                            </h3>
                            <p class="text-xs text-gray-400 mb-3">
                                {{ $loan->book->penulis }}
                            </p>

                            <div class="mt-auto space-y-1 text-xs text-gray-400 border-t border-gray-700 pt-2">
                                <div class="flex justify-between">
                                    <span>Tgl Pinjam:</span>
                                    <span class="text-gray-300">{{ $loan->tanggal_pinjam ? $loan->tanggal_pinjam->format('d M Y') : '-' }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span>Batas Kembali:</span>
                                    <span class="{{ $loan->status == 'dipinjam' && now()->gt($loan->tanggal_harus_kembali) ? 'text-red-400 font-bold' : 'text-gray-300' }}">
                                        {{ $loan->tanggal_harus_kembali ? $loan->tanggal_harus_kembali->format('d M Y') : '-' }}
                                    </span>
                                </div>
                            </div>

                            <div class="mt-4">
                                @if($loan->status == 'dipinjam')
                                    <a href="#"
                                       class="block w-full text-center rounded-lg bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                                        Baca E-Book
                                    </a>
                                @elseif($loan->status == 'pending')
                                    <button disabled class="block w-full text-center rounded-lg bg-gray-700 px-3 py-2 text-sm font-semibold text-gray-400 cursor-not-allowed">
                                        Menunggu Persetujuan
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full flex flex-col items-center justify-center py-12 text-center border border-dashed border-gray-700 rounded-xl bg-gray-800/30">
                        <svg class="w-12 h-12 text-gray-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                        <h3 class="text-lg font-medium text-white">Tidak ada buku yang dipinjam</h3>
                        <p class="text-gray-500 mt-1">Ayo cari buku menarik di koleksi kami!</p>
                        <a href="{{ route('user.koleksi') }}" class="mt-4 text-indigo-400 hover:text-indigo-300 text-sm font-medium">
                            Cari Buku &rarr;
                        </a>
                    </div>
                @endforelse
            </div>
        @endif

        @if($tab === 'history')
            <div class="bg-gray-800 rounded-xl border border-gray-700 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-700">
                        <thead class="bg-gray-900/50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Buku</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Tgl Pinjam</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Tgl Kembali</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-700 bg-gray-800">
                            @forelse($this->historyLoans as $history)
                                <tr class="hover:bg-gray-700/50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="h-10 w-8 flex-shrink-0">
                                                <img class="h-10 w-8 rounded object-cover" src="{{ $this->getCoverUrl($history->book->gambar_cover) }}" alt="">
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-white">{{ $history->book->judul }}</div>
                                                <div class="text-xs text-gray-500">{{ $history->book->category->nama_kategori }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">
                                        {{ $history->tanggal_pinjam ? $history->tanggal_pinjam->format('d M Y') : '-' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">
                                        {{ $history->tanggal_harus_kembali ? $history->tanggal_harus_kembali->format('d M Y') : '-' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $this->getStatusColor($history->status) }}">
                                            {{ ucfirst($history->status) }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-8 text-center text-gray-500 text-sm">
                                        Belum ada riwayat peminjaman.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        @if($tab === 'reviews')
             <div class="grid grid-cols-1 gap-4">
                @forelse($this->myReviews as $review)
                    <div class="bg-gray-800 rounded-xl border border-gray-700 p-4 flex gap-4">
                         <img src="{{ $this->getCoverUrl($review->book->gambar_cover) }}"
                              class="w-16 h-24 object-cover rounded-md shadow-sm shrink-0">

                         <div class="flex-1">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h4 class="text-white font-medium">{{ $review->book->judul }}</h4>
                                    <span class="text-xs text-gray-500">{{ $review->created_at->diffForHumans() }}</span>
                                </div>
                                {{-- Bintang Rating --}}
                                <div class="flex items-center bg-gray-900 rounded-lg px-2 py-1">
                                    <svg class="w-4 h-4 text-yellow-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.87 5.766a1 1 0 00.95.69h6.05c.969 0 1.372 1.24.588 1.81l-4.89 3.55a1 1 0 00-.364 1.118l1.87 5.766c.3.921-.755 1.688-1.54 1.118l-4.89-3.55a1 1 0 00-1.176 0l-4.89 3.55c-.784.57-1.838-.197-1.54-1.118l1.87-5.766a1 1 0 00-.364-1.118L.587 11.193c-.784-.57-.38-1.81.588-1.81h6.05a1 1 0 00.95-.69L9.049 2.927z"/></svg>
                                    <span class="ml-1 text-sm font-bold text-white">{{ $review->rating }}</span>
                                </div>
                            </div>

                            <p class="mt-2 text-sm text-gray-300 italic">
                                "{{ $review->ulasan }}"
                            </p>
                         </div>
                    </div>
                @empty
                     <div class="flex flex-col items-center justify-center py-12 text-center border border-dashed border-gray-700 rounded-xl bg-gray-800/30">
                        <div class="text-gray-600 mb-2">
                             <svg class="w-10 h-10 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                        </div>
                        <h3 class="text-lg font-medium text-white">Belum ada ulasan</h3>
                        <p class="text-gray-500 mt-1">Baca buku dan bagikan pendapatmu!</p>
                    </div>
                @endforelse
             </div>
        @endif

    </div>
</div>
