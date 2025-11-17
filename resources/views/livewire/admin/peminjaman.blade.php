{{--
======================================================================
    BAGIAN VIEW (HTML/BLADE) - HARUS ADA DI ATAS
======================================================================
--}}
<div>
    <main class="flex-1 p-6 lg:p-10">
        {{-- HEADER --}}
        <header class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                Manajemen Peminjaman
            </h1>
            <p class="mt-1 text-gray-600 dark:text-gray-400">
                Setujui, tolak, atau tandai selesai peminjaman buku.
            </p>
        </header>

        {{-- NOTIFIKASI --}}
        <div class="mb-6">
            @if (session('success'))
                <div class="p-4 rounded-md bg-green-100 text-green-800 border border-green-200"
                    x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)" x-transition>
                    {{ session('success') }}
                </div>
            @endif
            @if (session('error'))
                <div class="p-4 rounded-md bg-red-100 text-red-800 border border-red-200"
                    x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)" x-transition>
                    {{ session('error') }}
                </div>
            @endif
        </div>

        {{-- KARTU STATISTIK --}}
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4 mb-8">
            <div class="p-6 bg-white rounded-lg shadow-md dark:bg-gray-800">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500 uppercase dark:text-gray-400">Menunggu Persetujuan</p>
                        <p class="text-3xl font-bold text-yellow-600 dark:text-yellow-500">{{ $pendingCount }}</p>
                    </div>
                    <span class="p-3 bg-yellow-100 rounded-full dark:bg-yellow-900">
                        <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-300" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </span>
                </div>
            </div>
            <div class="p-6 bg-white rounded-lg shadow-md dark:bg-gray-800">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500 uppercase dark:text-gray-400">Sedang Dipinjam</p>
                        <p class="text-3xl font-bold text-blue-600 dark:text-blue-500">{{ $dipinjamCount }}</p>
                    </div>
                    <span class="p-3 bg-blue-100 rounded-full dark:bg-blue-900">
                        <svg class="w-6 h-6 text-blue-600 dark:text-blue-300" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" />
                        </svg>
                    </span>
                </div>
            </div>
        </div>

        {{-- AREA KONTEN UTAMA (FORM & TABEL) --}}
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">

            {{-- Kolom Kiri: FORM CREATE MANUAL --}}
            <div class="lg:col-span-1">
                <div class="p-6 bg-white rounded-lg shadow-md dark:bg-gray-800">
                    <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">
                        Buat Peminjaman Manual
                    </h3>

                    <form wire:submit="saveManual" class="space-y-4">
                        {{-- Member --}}
                        <div>
                            <label for="user_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Member</label>
                            <select id="user_id" wire:model="user_id" class="block w-full mt-1 px-3 py-2 border border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('user_id') border-red-500 @enderror">
                                <option value="">Pilih Member</option>
                                @foreach($users as $user)
                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                                @endforeach
                            </select>
                            @error('user_id') <span class="text-red-600 text-sm mt-1">{{ $message }}</span> @enderror
                        </div>

                        {{-- Buku --}}
                        <div>
                            <label for="book_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Buku</label>
                            <select id="book_id" wire:model="book_id" class="block w-full mt-1 px-3 py-2 border border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('book_id') border-red-500 @enderror">
                                <option value="">Pilih Buku</option>
                                @foreach($books as $book)
                                <option value="{{ $book->id }}">{{ $book->judul }}</option>
                                @endforeach
                            </select>
                            @error('book_id') <span class="text-red-600 text-sm mt-1">{{ $message }}</span> @enderror
                        </div>

                        {{-- Tanggal Pinjam --}}
                        <div>
                            <label for="tanggal_pinjam" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tanggal Pinjam</label>
                            <input type="date" id="tanggal_pinjam" wire:model="tanggal_pinjam" class="block w-full mt-1 px-3 py-2 border border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('tanggal_pinjam') border-red-500 @enderror">
                            @error('tanggal_pinjam') <span class="text-red-600 text-sm mt-1">{{ $message }}</span> @enderror
                        </div>

                        {{-- Tanggal Harus Kembali --}}
                        <div>
                            <label for="tanggal_harus_kembali" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tanggal Harus Kembali</label>
                            <input type="date" id="tanggal_harus_kembali" wire:model="tanggal_harus_kembali" class="block w-full mt-1 px-3 py-2 border border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('tanggal_harus_kembali') border-red-500 @enderror">
                            @error('tanggal_harus_kembali') <span class="text-red-600 text-sm mt-1">{{ $message }}</span> @enderror
                        </div>

                        {{-- Status --}}
                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Status</label>
                            <select id="status" wire:model="status" class="block w-full mt-1 px-3 py-2 border border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('status') border-red-500 @enderror">
                                @foreach($statuses as $s)
                                <option value="{{ $s }}">{{ ucfirst($s) }}</option>
                                @endforeach
                            </select>
                            @error('status') <span class="text-red-600 text-sm mt-1">{{ $message }}</span> @enderror
                        </div>

                        <button type="submit"
                                class="w-full px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-md shadow-sm hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800">
                            Simpan Peminjaman Manual
                        </button>
                    </form>
                </div>
            </div>

            {{-- Kolom Kanan: TABEL DAFTAR PEMINJAMAN --}}
            <div class="lg:col-span-2">
                <div class="p-6 bg-white rounded-lg shadow-md dark:bg-gray-800">
                    <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">Daftar Peminjaman</h3>

                    <div class="overflow-x-auto">
                        <table class="w-full min-w-full text-left align-middle">
                            <thead class="border-b border-gray-200 dark:border-gray-700">
                                <tr class="text-xs font-medium text-gray-500 uppercase dark:text-gray-400">
                                    <th class="px-4 py-3">Member</th>
                                    <th class="px-4 py-3">Buku</th>
                                    <th class="px-4 py-3">Tanggal</th>
                                    <th class="px-4 py-3">Status</th>
                                    <th class="px-4 py-3 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse ($peminjamans as $peminjaman)
                                    <tr wire:key="{{ $peminjaman->id }}" class="text-sm text-gray-900 dark:text-white">
                                        <td class="px-4 py-3">
                                            <div class="font-medium">{{ $peminjaman->user->name ?? 'User Dihapus' }}</div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400">{{ $peminjaman->user->email ?? '-' }}</div>
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="font-medium">{{ $peminjaman->book->judul ?? 'Buku Dihapus' }}</div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400">{{ $peminjaman->book->penulis ?? '-' }}</div>
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="font-medium">Pinjam: {{ $peminjaman->tanggal_pinjam ? $peminjaman->tanggal_pinjam->format('d M Y') : '-' }}</div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400">Kembali: {{ $peminjaman->tanggal_harus_kembali ? $peminjaman->tanggal_harus_kembali->format('d M Y') : '-' }}</div>
                                        </td>
                                        <td class="px-4 py-3">
                                            @if ($peminjaman->status == 'pending')
                                                <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">Pending</span>
                                            @elseif ($peminjaman->status == 'dipinjam')
                                                <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">Dipinjam</span>
                                            @elseif ($peminjaman->status == 'selesai')
                                                <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">Selesai</span>
                                            @elseif ($peminjaman->status == 'ditolak')
                                                <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">Ditolak</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-right font-medium space-x-2">
                                            {{-- TOMBOL AKSI DINAMIS --}}
                                            @if ($peminjaman->status == 'pending')
                                                <button type="button" wire:click="approve({{ $peminjaman->id }})" class="px-2 py-1 bg-green-500 text-white text-xs font-medium rounded-md hover:bg-green-600" title="Setujui">Setujui</button>
                                                <button type="button" wire:click="reject({{ $peminjaman->id }})" class="px-2 py-1 bg-red-600 text-white text-xs font-medium rounded-md hover:bg-red-700" title="Tolak">Tolak</button>
                                            @elseif ($peminjaman->status == 'dipinjam')
                                                <button type="button" wire:click="markAsReturned({{ $peminjaman->id }})" class="px-2 py-1 bg-blue-600 text-white text-xs font-medium rounded-md hover:bg-blue-700" title="Tandai Selesai">Selesai</button>
                                            @endif

                                            {{-- Tombol Hapus (selalu ada) --}}
                                            <button type="button" wire:click="delete({{ $peminjaman->id }})" wire:confirm="Anda yakin ingin menghapus data peminjaman ini?" class="px-2 py-1 bg-gray-400 text-white text-xs font-medium rounded-md hover:bg-gray-500" title="Hapus Data">
                                                Hapus
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-4 py-4 text-center text-sm text-gray-500 dark:text-gray-400">
                                            Belum ada data peminjaman.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Link Paginasi --}}
                    <div class="mt-6">
                        {{ $peminjamans->links() }}
                    </div>
                </div>
            </div>

        </div>
    </main>
</div>

<?php

use Livewire\Volt\Component;
use App\Models\Peminjaman;
use App\Models\User;
use App\Models\Book;
use Livewire\WithPagination;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

// Komponen Volt
new class extends Component
{
    use WithPagination;

    // Properti untuk Form Create Manual
    public $user_id = '';
    public $book_id = '';
    public $tanggal_pinjam;
    public $tanggal_harus_kembali;
    public $status = 'dipinjam'; // Default untuk pinjam manual

    // Daftar status dari ENUM di migrasi
    public $statuses = ['pending', 'dipinjam', 'selesai', 'ditolak'];

    /**
     * Terapkan validasi untuk form create manual.
     */
    protected function rules()
    {
        return [
            'user_id' => ['required', 'exists:users,id'],
            'book_id' => ['required', 'exists:books,id'],
            'status' => ['required', Rule::in($this->statuses)],

            // Tanggal wajib diisi jika statusnya 'dipinjam' atau 'selesai'
            'tanggal_pinjam' => [
                'required_if:status,dipinjam,selesai',
                'nullable',
                'date'
            ],
            'tanggal_harus_kembali' => [
                'required_if:status,dipinjam',
                'nullable',
                'date',
                'after_or_equal:tanggal_pinjam'
            ],
        ];
    }

    /**
     * Pesan validasi kustom
     */
    protected $messages = [
        'user_id.required' => 'Member harus dipilih.',
        'book_id.required' => 'Buku harus dipilih.',
        'tanggal_pinjam.required_if' => 'Tanggal pinjam harus diisi untuk status ini.',
        'tanggal_harus_kembali.required_if' => 'Tanggal kembali harus diisi untuk status ini.',
        'tanggal_harus_kembali.after_or_equal' => 'Tgl kembali tidak boleh sebelum tgl pinjam.',
    ];

    /**
     * Mengambil data untuk view.
     */
    public function with(): array
    {
        return [
            // Eager load relasi 'user' dan 'book'
            'peminjamans' => Peminjaman::with(['user', 'book'])
                                        ->orderBy('created_at', 'desc') // Tampilkan yang terbaru dulu
                                        ->paginate(10),

            // Ambil data untuk dropdown di form
            'users' => User::where('role', 'member')->orderBy('name', 'asc')->get(),
            'books' => Book::orderBy('judul', 'asc')->get(),

            // Data untuk kartu statistik
            'pendingCount' => Peminjaman::where('status', 'pending')->count(),
            'dipinjamCount' => Peminjaman::where('status', 'dipinjam')->count(),
        ];
    }

    /**
     * Method untuk CREATE MANUAL (dari form kiri).
     */
    public function saveManual()
    {
        $validated = $this->validate();

        try {
            Peminjaman::create($validated);
            session()->flash('success', 'Peminjaman manual berhasil ditambahkan.');
            $this->resetForm();

        } catch (\Exception $e) {
            session()->flash('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    // --- QUICK ACTIONS UNTUK MANAJEMEN STATUS ---

    /**
     * Menyetujui peminjaman yang 'pending'.
     */
    public function approve(Peminjaman $peminjaman)
    {
        if ($peminjaman->status === 'pending') {
            $peminjaman->update([
                'status' => 'dipinjam',
                'tanggal_pinjam' => Carbon::now()->toDateString(),
                'tanggal_harus_kembali' => Carbon::now()->addDays(7)->toDateString(), // Asumsi 7 hari
            ]);
            session()->flash('success', 'Peminjaman disetujui. Buku siap diambil.');
        }
    }

    /**
     * Menolak peminjaman yang 'pending'.
     */
    public function reject(Peminjaman $peminjaman)
    {
        if ($peminjaman->status === 'pending') {
            $peminjaman->update(['status' => 'ditolak']);
            session()->flash('success', 'Peminjaman ditolak.');
        }
    }

    /**
     * Menandai buku yang 'dipinjam' sebagai 'selesai' (telah dikembalikan).
     */
    public function markAsReturned(Peminjaman $peminjaman)
    {
        if ($peminjaman->status === 'dipinjam') {
            // Migrasi Anda tidak punya 'tanggal_kembali_aktual',
            // jadi kita hanya ubah statusnya.
            $peminjaman->update(['status' => 'selesai']);
            session()->flash('success', 'Buku telah ditandai sebagai selesai/dikembalikan.');
        }
    }

    /**
     * (DELETE) Menghapus data peminjaman (jika ada kesalahan input).
     */
    public function delete(Peminjaman $peminjaman)
    {
        try {
            $peminjaman->delete();
            session()->flash('success', 'Data peminjaman berhasil dihapus.');
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal menghapus data.');
        }
    }

    /**
     * Helper internal untuk mereset form.
     */
    private function resetForm()
    {
        $this->reset('user_id', 'book_id', 'tanggal_pinjam', 'tanggal_harus_kembali', 'status');
        $this->status = 'dipinjam'; // Kembalikan ke default
        $this->resetErrorBag();
    }
}; ?>
