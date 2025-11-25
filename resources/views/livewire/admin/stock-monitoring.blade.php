<?php

use Livewire\Volt\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Url;
use App\Models\Book;

new class extends Component
{
    use WithPagination;

    #[Url]
    public $search = '';

    public function with(): array
    {
        // Base Query: Ambil buku dengan lisensi kurang dari 5
        $query = Book::where('lisensi', '<', 5);

        // Fitur Pencarian
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('judul', 'like', '%' . $this->search . '%')
                  ->orWhere('penulis', 'like', '%' . $this->search . '%');
            });
        }

        // Statistik Real-time
        $stats = [
            'empty' => Book::where('lisensi', 0)->count(),
            'critical' => Book::whereBetween('lisensi', [1, 2])->count(),
            'warning' => Book::whereBetween('lisensi', [3, 4])->count(),
        ];

        return [
            'books' => $query->orderBy('lisensi', 'asc') // Prioritaskan yang kosong (0)
                           ->paginate(10),
            'stats' => $stats,
        ];
    }

    // Reset halaman saat mencari
    public function updatedSearch()
    {
        $this->resetPage();
    }
}; ?>

<div>
    <main class="flex-1 p-6 lg:p-10 bg-gray-50 dark:bg-gray-900 min-h-screen">

        {{-- HEADER --}}
        <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8 gap-4">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white tracking-tight">
                    Pemantauan Stok Buku
                </h1>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    Prioritas pengadaan untuk buku dengan stok lisensi menipis atau kosong.
                </p>
            </div>

            <button class="flex items-center justify-center px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition shadow-sm">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                Unduh Laporan
            </button>
        </div>

        {{-- KARTU STATISTIK --}}
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-3 mb-8">
            <!-- Card 1: Stok Kosong (Urgent) -->
            <div class="p-5 bg-white border-l-4 border-red-500 rounded-r-xl shadow-sm hover:shadow-md transition-shadow dark:bg-gray-800 dark:border-red-600">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold text-red-600 uppercase tracking-wide dark:text-red-400">Stok Habis (0)</p>
                        <p class="text-3xl font-bold text-gray-900 dark:text-white mt-1">{{ $stats['empty'] }}</p>
                        <p class="text-xs text-gray-500 mt-1">Perlu tindakan segera</p>
                    </div>
                    <div class="p-3 bg-red-50 rounded-full dark:bg-red-900/20">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-red-600 dark:text-red-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                    </div>
                </div>
            </div>

            <!-- Card 2: Sangat Kritis (1-2) -->
            <div class="p-5 bg-white border-l-4 border-orange-500 rounded-r-xl shadow-sm hover:shadow-md transition-shadow dark:bg-gray-800 dark:border-orange-600">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold text-orange-600 uppercase tracking-wide dark:text-orange-400">Sangat Kritis (1-2)</p>
                        <p class="text-3xl font-bold text-gray-900 dark:text-white mt-1">{{ $stats['critical'] }}</p>
                        <p class="text-xs text-gray-500 mt-1">Hampir habis</p>
                    </div>
                    <div class="p-3 bg-orange-50 rounded-full dark:bg-orange-900/20">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-orange-600 dark:text-orange-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                    </div>
                </div>
            </div>

            <!-- Card 3: Perlu Restock (3-4) -->
            <div class="p-5 bg-white border-l-4 border-yellow-400 rounded-r-xl shadow-sm hover:shadow-md transition-shadow dark:bg-gray-800 dark:border-yellow-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold text-yellow-600 uppercase tracking-wide dark:text-yellow-400">Menipis (3-4)</p>
                        <p class="text-3xl font-bold text-gray-900 dark:text-white mt-1">{{ $stats['warning'] }}</p>
                        <p class="text-xs text-gray-500 mt-1">Siapkan pengadaan</p>
                    </div>
                    <div class="p-3 bg-yellow-50 rounded-full dark:bg-yellow-900/20">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-yellow-600 dark:text-yellow-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.24 12.24a6 6 0 0 0-8.49-8.49L5 10.5V19h8.5z"/><line x1="16" y1="8" x2="2" y2="22"/><line x1="17.5" y1="15" x2="9" y2="15"/></svg>
                    </div>
                </div>
            </div>
        </div>

        {{-- SEARCH BAR --}}
        <div class="mb-6 relative max-w-md">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>
            <input wire:model.live.debounce.300ms="search" type="text" placeholder="Cari judul buku atau penulis..."
                class="block w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 sm:text-sm dark:bg-gray-800 dark:border-gray-700 dark:text-white transition duration-150 ease-in-out">
        </div>

        {{-- TABLE CONTENT --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden dark:bg-gray-800 dark:border-gray-700">

            <!-- Loading Overlay -->
            <div wire:loading.flex wire:target="search, page" class="absolute inset-0 bg-white/50 dark:bg-gray-900/50 z-10 items-center justify-center hidden">
                <svg class="animate-spin h-8 w-8 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-gray-50 dark:bg-gray-700/50">
                        <tr>
                            <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider dark:text-gray-400">Buku</th>
                            <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider dark:text-gray-400">Status Stok</th>
                            <th class="px-6 py-4 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider dark:text-gray-400">Total Lisensi</th>
                            <th class="px-6 py-4 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider dark:text-gray-400">Sedang Dipinjam</th>
                            <th class="px-6 py-4 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider dark:text-gray-400">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse ($books as $book)
                            <tr wire:key="{{ $book->id }}" class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                                {{-- Judul & Penulis --}}
                                <td class="px-6 py-4">
                                    <div class="flex flex-col">
                                        <span class="text-sm font-medium text-gray-900 dark:text-white line-clamp-1" title="{{ $book->judul }}">
                                            {{ $book->judul }}
                                        </span>
                                        <div class="flex items-center mt-1 text-xs text-gray-500 dark:text-gray-400">
                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                            {{ $book->penulis ?? 'Tanpa Penulis' }}
                                        </div>
                                    </div>
                                </td>

                                {{-- Status Badge --}}
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if ($book->lisensi == 0)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300">
                                            <span class="w-2 h-2 mr-1.5 bg-red-500 rounded-full"></span>
                                            Habis
                                        </span>
                                    @elseif ($book->lisensi <= 2)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-300">
                                            <span class="w-2 h-2 mr-1.5 bg-orange-500 rounded-full"></span>
                                            Sangat Kritis
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300">
                                            <span class="w-2 h-2 mr-1.5 bg-yellow-500 rounded-full"></span>
                                            Menipis
                                        </span>
                                    @endif
                                </td>

                                {{-- Jumlah Lisensi (Angka Besar) --}}
                                <td class="px-6 py-4 text-center">
                                    <span class="text-lg font-bold {{ $book->lisensi == 0 ? 'text-red-600 dark:text-red-400' : 'text-gray-900 dark:text-white' }}">
                                        {{ $book->lisensi }}
                                    </span>
                                </td>

                                {{-- Sedang Dipinjam --}}
                                <td class="px-6 py-4 text-center">
                                    <div class="inline-flex items-center px-3 py-1 bg-gray-100 text-gray-700 rounded-md text-sm font-medium dark:bg-gray-700 dark:text-gray-300">
                                        {{ $book->jumlah_dipinjam }}
                                    </div>
                                </td>

                                {{-- Aksi --}}
                                <td class="px-6 py-4 text-right whitespace-nowrap text-sm font-medium">
                                    <a href="#" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300 hover:underline">
                                        + Tambah Stok
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center justify-center">
                                        <div class="bg-green-100 p-4 rounded-full dark:bg-green-900/20 mb-4">
                                            <svg class="h-8 w-8 text-green-600 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                        </div>
                                        <h3 class="text-lg font-medium text-gray-900 dark:text-white">Stok Aman!</h3>
                                        <p class="text-gray-500 dark:text-gray-400 mt-1">
                                            Tidak ada buku dengan lisensi di bawah 5. Kerja bagus!
                                        </p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination Footer --}}
            <div class="bg-gray-50 px-6 py-4 border-t border-gray-200 dark:bg-gray-800 dark:border-gray-700">
                {{ $books->links() }}
            </div>
        </div>
    </main>
</div>
