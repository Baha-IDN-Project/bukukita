<?php

use Livewire\Volt\Component;
use App\Models\Peminjaman;
use App\Models\User;
use App\Models\Book;
use Livewire\WithPagination;
use Livewire\Attributes\Url;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

new class extends Component
{
    use WithPagination;

    // --- Filter & Search ---
    #[Url]
    public $search = '';

    #[Url]
    public $filterStatus = '';

    // --- Form Create Manual ---
    public $user_id = '';
    public $book_id = '';
    public $tanggal_pinjam;
    public $tanggal_harus_kembali;
    public $status = 'dipinjam';

    // Opsi Status
    public $statuses = ['pending', 'dipinjam', 'selesai', 'ditolak'];

    // --- Query Data ---
    public function with(): array
    {
        // Query Dasar
        $query = Peminjaman::with(['user', 'book'])->latest();

        // 1. Logic Pencarian
        if ($this->search) {
            $query->where(function ($q) {
                $q->whereHas('user', fn($u) => $u->where('name', 'like', '%' . $this->search . '%'))
                  ->orWhereHas('book', fn($b) => $b->where('judul', 'like', '%' . $this->search . '%'));
            });
        }

        // 2. Logic Filter Status
        if ($this->filterStatus) {
            $query->where('status', $this->filterStatus);
        }

        return [
            'peminjamans' => $query->paginate(10),

            // Data Dropdown
            'users' => User::where('role', 'member')->orderBy('name', 'asc')->get(),
            'books' => Book::orderBy('judul', 'asc')->get(),

            // Statistik Real-time
            'stats' => [
                'pending' => Peminjaman::where('status', 'pending')->count(),
                'active'  => Peminjaman::where('status', 'dipinjam')->count(),
                'returned'=> Peminjaman::where('status', 'selesai')->count(),
            ]
        ];
    }

    // Reset pagination saat search/filter berubah
    public function updatedSearch() { $this->resetPage(); }
    public function updatedFilterStatus() { $this->resetPage(); }

    // --- Logic Form & Actions (Sama seperti sebelumnya, dirapikan) ---

    protected function rules()
    {
        return [
            'user_id' => ['required', 'exists:users,id'],
            'book_id' => ['required', 'exists:books,id'],
            'status' => ['required', Rule::in($this->statuses)],
            'tanggal_pinjam' => ['required_if:status,dipinjam,selesai', 'nullable', 'date'],
            'tanggal_harus_kembali' => ['required_if:status,dipinjam', 'nullable', 'date', 'after_or_equal:tanggal_pinjam'],
        ];
    }

    public function saveManual()
    {
        $validated = $this->validate();
        try {
            Peminjaman::create($validated);
            session()->flash('success', 'Peminjaman manual berhasil dibuat.');
            $this->resetForm();
        } catch (\Exception $e) {
            session()->flash('error', 'Error: ' . $e->getMessage());
        }
    }

    public function approve(Peminjaman $peminjaman)
    {
        if ($peminjaman->book->jumlah_dipinjam >= $peminjaman->book->lisensi) {
            session()->flash('error', 'Gagal: Stok buku habis.');
            return;
        }
        if ($peminjaman->status === 'pending') {
            $peminjaman->update([
                'status' => 'dipinjam',
                'tanggal_pinjam' => Carbon::now(),
                'tanggal_harus_kembali' => Carbon::now()->addDays(7),
            ]);
            $peminjaman->book->increment('jumlah_dipinjam');
            session()->flash('success', 'Peminjaman disetujui.');
        }
    }

    public function reject(Peminjaman $peminjaman)
    {
        if ($peminjaman->status === 'pending') {
            $peminjaman->update(['status' => 'ditolak']);
            session()->flash('success', 'Peminjaman ditolak.');
        }
    }

    public function markAsReturned(Peminjaman $peminjaman)
    {
        if ($peminjaman->status === 'dipinjam') {
            $peminjaman->update(['status' => 'selesai']);
            if ($peminjaman->book->jumlah_dipinjam > 0) {
                $peminjaman->book->decrement('jumlah_dipinjam');
            }
            session()->flash('success', 'Buku dikembalikan.');
        }
    }

    public function delete(Peminjaman $peminjaman)
    {
        // Jika menghapus yang sedang dipinjam, kembalikan stok
        if ($peminjaman->status === 'dipinjam' && $peminjaman->book->jumlah_dipinjam > 0) {
            $peminjaman->book->decrement('jumlah_dipinjam');
        }
        $peminjaman->delete();
        session()->flash('success', 'Data dihapus.');
    }

    private function resetForm()
    {
        $this->reset('user_id', 'book_id', 'tanggal_pinjam', 'tanggal_harus_kembali', 'status');
        $this->status = 'dipinjam';
        $this->resetErrorBag();
    }
}; ?>

<div>
    <main class="flex-1 p-6 lg:p-10 bg-gray-50 dark:bg-gray-900 min-h-screen">

        {{-- HEADER --}}
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white tracking-tight">
                Manajemen Peminjaman
            </h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                Kelola sirkulasi buku, persetujuan, dan pengembalian.
            </p>
        </div>

        {{-- FLASH MESSAGE --}}
        <div class="fixed top-5 right-5 z-50 w-full max-w-sm">
            @if (session('success'))
                <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)" x-transition
                    class="p-4 mb-2 bg-green-50 text-green-800 border-l-4 border-green-500 rounded shadow-lg flex items-center dark:bg-green-900 dark:text-green-100">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    {{ session('success') }}
                </div>
            @endif
            @if (session('error'))
                <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)" x-transition
                    class="p-4 mb-2 bg-red-50 text-red-800 border-l-4 border-red-500 rounded shadow-lg flex items-center dark:bg-red-900 dark:text-red-100">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    {{ session('error') }}
                </div>
            @endif
        </div>

        {{-- STATISTIK CARDS --}}
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-3 mb-8">
            <!-- Menunggu -->
            <div class="p-5 bg-white border border-gray-100 rounded-xl shadow-sm dark:bg-gray-800 dark:border-gray-700">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold text-yellow-600 uppercase tracking-wide dark:text-yellow-400">Menunggu (Pending)</p>
                        <p class="text-3xl font-bold text-gray-900 dark:text-white mt-1">{{ $stats['pending'] }}</p>
                    </div>
                    <div class="p-3 bg-yellow-50 rounded-full dark:bg-yellow-900/20 text-yellow-600 dark:text-yellow-400">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                </div>
            </div>

            <!-- Sedang Dipinjam -->
            <div class="p-5 bg-white border border-gray-100 rounded-xl shadow-sm dark:bg-gray-800 dark:border-gray-700">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold text-blue-600 uppercase tracking-wide dark:text-blue-400">Sedang Dipinjam</p>
                        <p class="text-3xl font-bold text-gray-900 dark:text-white mt-1">{{ $stats['active'] }}</p>
                    </div>
                    <div class="p-3 bg-blue-50 rounded-full dark:bg-blue-900/20 text-blue-600 dark:text-blue-400">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                    </div>
                </div>
            </div>

            <!-- Selesai -->
            <div class="p-5 bg-white border border-gray-100 rounded-xl shadow-sm dark:bg-gray-800 dark:border-gray-700">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold text-green-600 uppercase tracking-wide dark:text-green-400">Selesai / Kembali</p>
                        <p class="text-3xl font-bold text-gray-900 dark:text-white mt-1">{{ $stats['returned'] }}</p>
                    </div>
                    <div class="p-3 bg-green-50 rounded-full dark:bg-green-900/20 text-green-600 dark:text-green-400">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                </div>
            </div>
        </div>

        {{-- LAYOUT UTAMA --}}
        <div class="grid grid-cols-1 gap-8 lg:grid-cols-3 items-start">

            {{-- FORM CREATE (KIRI) --}}
            <div class="lg:col-span-1 lg:sticky lg:top-6">
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 dark:bg-gray-800 dark:border-gray-700">
                    <div class="p-6 border-b border-gray-100 dark:border-gray-700">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                            <svg class="w-5 h-5 mr-2 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            Input Peminjaman
                        </h3>
                    </div>

                    <div class="p-6">
                        <form wire:submit="saveManual" class="space-y-4">
                            <div>
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Member</label>
                                <select wire:model="user_id" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                    <option value="">-- Pilih Member --</option>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                                    @endforeach
                                </select>
                                @error('user_id') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Buku</label>
                                <select wire:model="book_id" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                    <option value="">-- Pilih Buku --</option>
                                    @foreach($books as $book)
                                        <option value="{{ $book->id }}">{{ $book->judul }}</option>
                                    @endforeach
                                </select>
                                @error('book_id') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                            </div>

                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Tgl Pinjam</label>
                                    <input type="date" wire:model="tanggal_pinjam" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                    @error('tanggal_pinjam') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Tgl Kembali</label>
                                    <input type="date" wire:model="tanggal_harus_kembali" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                    @error('tanggal_harus_kembali') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Status Awal</label>
                                <select wire:model="status" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                    @foreach($statuses as $s)
                                        <option value="{{ $s }}">{{ ucfirst($s) }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <button type="submit" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition">
                                <span wire:loading.remove wire:target="saveManual">Simpan Data</span>
                                <span wire:loading wire:target="saveManual">Menyimpan...</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            {{-- TABLE DATA (KANAN) --}}
            <div class="lg:col-span-2">

                {{-- Toolbar Table (Search & Filter) --}}
                <div class="flex flex-col sm:flex-row gap-4 mb-4">
                    <div class="relative flex-1">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </div>
                        <input wire:model.live.debounce.300ms="search" type="text" placeholder="Cari member atau buku..." class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg leading-5 bg-white placeholder-gray-500 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm dark:bg-gray-800 dark:border-gray-700 dark:text-white">
                    </div>
                    <div class="w-full sm:w-48">
                        <select wire:model.live="filterStatus" class="block w-full pl-3 pr-10 py-2 border border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-lg dark:bg-gray-800 dark:border-gray-700 dark:text-white">
                            <option value="">Semua Status</option>
                            @foreach($statuses as $s)
                                <option value="{{ $s }}">{{ ucfirst($s) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- The Table --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden dark:bg-gray-800 dark:border-gray-700 relative">

                    {{-- Loading Overlay --}}
                    <div wire:loading.flex wire:target="search, filterStatus, page, approve, reject, markAsReturned" class="absolute inset-0 bg-white/50 dark:bg-gray-900/50 z-20 items-center justify-center hidden">
                        <svg class="animate-spin h-8 w-8 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead class="bg-gray-50 dark:bg-gray-700/50">
                                <tr>
                                    <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider dark:text-gray-400">Peminjam</th>
                                    <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider dark:text-gray-400">Buku & Stok</th>
                                    <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider dark:text-gray-400">Jadwal</th>
                                    <th class="px-6 py-4 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider dark:text-gray-400">Status</th>
                                    <th class="px-6 py-4 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider dark:text-gray-400">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse ($peminjamans as $pinjam)
                                    <tr wire:key="row-{{ $pinjam->id }}" class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition">
                                        {{-- Member Column --}}
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0 h-9 w-9">
                                                    <div class="h-9 w-9 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-700 font-bold text-xs dark:bg-indigo-900 dark:text-indigo-300">
                                                        {{ substr($pinjam->user->name ?? '?', 0, 2) }}
                                                    </div>
                                                </div>
                                                <div class="ml-3">
                                                    <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $pinjam->user->name ?? 'Deleted' }}</div>
                                                    <div class="text-xs text-gray-500 dark:text-gray-400">{{ $pinjam->user->email ?? '' }}</div>
                                                </div>
                                            </div>
                                        </td>

                                        {{-- Buku Column --}}
                                        <td class="px-6 py-4">
                                            <div class="text-sm font-medium text-gray-900 dark:text-white line-clamp-1" title="{{ $pinjam->book->judul ?? '' }}">
                                                {{ $pinjam->book->judul ?? 'Buku Dihapus' }}
                                            </div>
                                            <div class="flex items-center mt-1 text-xs text-gray-500">
                                                <span class="mr-2">Sisa Stok:</span>
                                                @php
                                                    $stok = ($pinjam->book->lisensi ?? 0) - ($pinjam->book->jumlah_dipinjam ?? 0);
                                                @endphp
                                                <span class="font-bold {{ $stok > 0 ? 'text-green-600' : 'text-red-600' }}">
                                                    {{ $stok }}
                                                </span>
                                                <span class="mx-1">/</span>
                                                <span>{{ $pinjam->book->lisensi ?? 0 }}</span>
                                            </div>
                                        </td>

                                        {{-- Jadwal Column --}}
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-xs text-gray-500 dark:text-gray-400 space-y-1">
                                                <div class="flex items-center">
                                                    <span class="w-14 text-gray-400">Pinjam:</span>
                                                    <span class="text-gray-700 dark:text-gray-200 font-medium">
                                                        {{ $pinjam->tanggal_pinjam ? \Carbon\Carbon::parse($pinjam->tanggal_pinjam)->format('d M Y') : '-' }}
                                                    </span>
                                                </div>
                                                <div class="flex items-center">
                                                    <span class="w-14 text-gray-400">Kembali:</span>
                                                    <span class="{{ $pinjam->status == 'dipinjam' && \Carbon\Carbon::parse($pinjam->tanggal_harus_kembali)->isPast() ? 'text-red-600 font-bold' : 'text-gray-700 dark:text-gray-200' }}">
                                                        {{ $pinjam->tanggal_harus_kembali ? \Carbon\Carbon::parse($pinjam->tanggal_harus_kembali)->format('d M Y') : '-' }}
                                                    </span>
                                                </div>
                                            </div>
                                        </td>

                                        {{-- Status Column --}}
                                        <td class="px-6 py-4 whitespace-nowrap text-center">
                                            @if ($pinjam->status == 'pending')
                                                <span class="px-2.5 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900/40 dark:text-yellow-300 border border-yellow-200 dark:border-yellow-800">Pending</span>
                                            @elseif ($pinjam->status == 'dipinjam')
                                                <span class="px-2.5 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300 border border-blue-200 dark:border-blue-800">Dipinjam</span>
                                            @elseif ($pinjam->status == 'selesai')
                                                <span class="px-2.5 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300 border border-green-200 dark:border-green-800">Selesai</span>
                                            @else
                                                <span class="px-2.5 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300 border border-red-200 dark:border-red-800">Ditolak</span>
                                            @endif
                                        </td>

                                        {{-- Action Buttons --}}
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                                            <div class="flex justify-end gap-2">
                                                @if ($pinjam->status == 'pending')
                                                    <button wire:click="approve({{ $pinjam->id }})" class="text-green-600 hover:text-green-900 bg-green-50 hover:bg-green-100 p-1.5 rounded-md transition" title="Setujui">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                                    </button>
                                                    <button wire:click="reject({{ $pinjam->id }})" class="text-red-600 hover:text-red-900 bg-red-50 hover:bg-red-100 p-1.5 rounded-md transition" title="Tolak">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                                    </button>
                                                @elseif ($pinjam->status == 'dipinjam')
                                                    <button wire:click="markAsReturned({{ $pinjam->id }})" class="text-blue-600 hover:text-blue-900 bg-blue-50 hover:bg-blue-100 px-3 py-1.5 rounded-md text-xs font-medium transition flex items-center" title="Tandai Kembali">
                                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                                                        Selesai
                                                    </button>
                                                @endif

                                                <button wire:click="delete({{ $pinjam->id }})" wire:confirm="Hapus data ini secara permanen?" class="text-gray-400 hover:text-red-600 p-1.5 transition ml-1" title="Hapus Data">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-6 py-10 text-center text-gray-500 dark:text-gray-400">
                                            <div class="flex flex-col items-center">
                                                <svg class="w-12 h-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                                                <p>Belum ada data peminjaman ditemukan.</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Footer --}}
                    <div class="bg-gray-50 px-6 py-4 border-t border-gray-200 dark:bg-gray-800 dark:border-gray-700">
                        {{ $peminjamans->links() }}
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>
