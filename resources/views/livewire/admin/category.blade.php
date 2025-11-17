<?php

use Livewire\Volt\Component;
use App\Models\Category; // Pastikan model ini ada
use Livewire\WithPagination;
use Illuminate\Validation\Rule;
use Illuminate\Database\QueryException;

// Komponen Volt
new class extends Component
{
    use WithPagination;

    // Properti untuk form
    public string $nama_kategori = '';

    // Properti untuk state Update
    public ?Category $editingCategory = null;

    /**
     * Terapkan validasi.
     * Kita menggunakan method agar validasi 'unique' bisa dinamis.
     */
    protected function rules()
    {
        return [
            'nama_kategori' => [
                'required',
                'string',
                'max:100',
                // Aturan 'unique' ini akan mengabaikan ID kategori
                // yang sedang diedit, sehingga tidak bentrok dengan dirinya sendiri.
                Rule::unique('categories')->ignore($this->editingCategory?->id),
            ],
        ];
    }

    /**
     * Pesan validasi kustom (Opsional)
     */
    protected $messages = [
        'nama_kategori.required' => 'Nama kategori tidak boleh kosong.',
        'nama_kategori.max' => 'Nama kategori maksimal 100 karakter.',
        'nama_kategori.unique' => 'Nama kategori ini sudah ada.',
    ];

    /**
     * Mengambil data kategori dengan paginasi.
     * 'with' closure ini akan dieksekusi setiap kali komponen dirender.
     */
    public function with(): array
    {
        return [
            'categories' => Category::orderBy('nama_kategori', 'asc')->paginate(10),
        ];
    }

    /**
     * Method untuk CREATE dan UPDATE.
     * Livewire akan otomatis memanggil $this->validate()
     * berdasarkan properti $rules.
     */
    public function save()
    {
        // Jalankan validasi
        $validated = $this->validate();

        try {
            if ($this->editingCategory) {
                // --- UPDATE ---
                $this->editingCategory->update($validated);
                session()->flash('success', 'Kategori berhasil diperbarui.');
            } else {
                // --- CREATE ---
                Category::create($validated);
                session()->flash('success', 'Kategori berhasil ditambahkan.');
            }

            // Reset form setelah sukses
            $this->resetForm();

        } catch (QueryException $e) {
            // Menangani error database (misal: unique constraint)
            // meskipun validasi di atas seharusnya sudah menangani
            session()->flash('error', 'Terjadi kesalahan database.');
        }
    }

    /**
     * (UPDATE) Menyiapkan form untuk mode edit.
     */
    public function edit(Category $category)
    {
        $this->editingCategory = $category;
        $this->nama_kategori = $category->nama_kategori;
        $this->resetErrorBag(); // Bersihkan error validasi sebelumnya
    }

    /**
     * (DELETE) Menghapus kategori.
     * Menggunakan konfirmasi bawaan Livewire 3 (wire:confirm).
     */
    public function delete(Category $category)
    {
        try {
            $category->delete();
            session()->flash('success', 'Kategori "' . $category->nama_kategori . '" berhasil dihapus.');

            // Jika yang dihapus adalah yang sedang diedit, reset form
            if ($this->editingCategory && $this->editingCategory->id === $category->id) {
                $this->resetForm();
            }

        } catch (QueryException $e) {
            // Menangani jika kategori tidak bisa dihapus (misal: karena relasi foreign key)
            session()->flash('error', 'Kategori tidak bisa dihapus, mungkin terkait dengan data buku.');
        }
    }

    /**
     * Helper untuk membatalkan mode edit / reset form.
     */
    public function cancelEdit()
    {
        $this->resetForm();
    }

    /**
     * Helper internal untuk mereset properti form.
     */
    private function resetForm()
    {
        $this->reset('nama_kategori');
        $this->editingCategory = null;
        $this->resetErrorBag(); // Bersihkan error validasi
    }
}; ?>

<div>
    <main class="flex-1 p-6 lg:p-10">
        {{-- HEADER --}}
        <header class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                Manajemen Kategori
            </h1>
            <p class="mt-1 text-gray-600 dark:text-gray-400">
                Tambah, edit, dan hapus kategori buku di perpustakaan.
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
                        <p class="text-sm font-medium text-gray-500 uppercase dark:text-gray-400">Total Kategori</p>
                        {{-- Kita menggunakan $categories->total() dari paginasi untuk mendapatkan total --}}
                        <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ $categories->total() }}</p>
                    </div>
                    <span class="p-3 bg-blue-100 rounded-full dark:bg-blue-900">
                        {{-- Icon untuk kategori (tag) --}}
                        {{-- <svg class="w-6 h-6 text-blue-600 dark:text-blue-300" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.73.53m-10.23-4.538L12.5 11.25m-1.25 0c-.621 0-1.125-.504-1.125-1.125S10.629 9 11.25 9s1.125.504 1.125 1.125S11.871 11.25 11.25 11.25z" />
                        </svg> --}}
                    </span>
                </div>
            </div>
            {{-- Anda bisa menambahkan kartu statistik lain di sini jika mau --}}
        </div>

        {{-- AREA KONTEN UTAMA (FORM & TABEL) --}}
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">

            {{-- Kolom Kiri: FORM CREATE & UPDATE --}}
            <div class="lg:col-span-1">
                <div class="p-6 bg-white rounded-lg shadow-md dark:bg-gray-800">
                    <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">
                        {{ $editingCategory ? 'Edit Kategori' : 'Tambah Kategori Baru' }}
                    </h3>

                    {{-- Form diletakkan di dalam card --}}
                    <form wire:submit="save" class="space-y-4">
                        <div>
                            <label for="nama_kategori" class="sr-only">Nama Kategori</label>
                            <input
                                type="text"
                                id="nama_kategori"
                                wire:model="nama_kategori"
                                placeholder="Contoh: Fiksi, Non-Fiksi, Sejarah"
                                class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm
                                       dark:bg-gray-700 dark:border-gray-600 dark:text-white
                                       focus:outline-none focus:ring-blue-500 focus:border-blue-500
                                       sm:text-sm
                                       @error('nama_kategori') border-red-500 @enderror">

                            @error('nama_kategori')
                                <span class="text-red-600 text-sm mt-2">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- Tombol Aksi Form --}}
                        <div class="flex items-center space-x-3">
                            @if ($editingCategory)
                                <button type="submit"
                                        class="w-full px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md shadow-sm
                                               hover:bg-blue-700 focus:outline-none focus:ring-2
                                               focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800">
                                    Update Kategori
                                </button>
                                <button type="button" wire:click="cancelEdit"
                                        class="w-full px-4 py-2 bg-gray-200 text-gray-700 text-sm font-medium rounded-md
                                               dark:bg-gray-600 dark:text-gray-200
                                               hover:bg-gray-300 dark:hover:bg-gray-500
                                               focus:outline-none focus:ring-2
                                               focus:ring-gray-400 focus:ring-offset-2 dark:focus:ring-offset-gray-800">
                                    Batal
                                </button>
                            @else
                                <button type="submit"
                                        class="w-full px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-md shadow-sm
                                               hover:bg-green-700 focus:outline-none focus:ring-2
                                               focus:ring-green-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800">
                                    Simpan Kategori Baru
                                </button>
                            @endif
                        </div>

                        {{-- Indikator loading saat menyimpan --}}
                        <div wire:loading wire:target="save">
                            <span class="text-sm text-gray-500 dark:text-gray-400">Menyimpan...</span>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Kolom Kanan: TABEL DAFTAR KATEGORI --}}
            <div class="lg:col-span-2">
                <div class="p-6 bg-white rounded-lg shadow-md dark:bg-gray-800">
                    <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">Daftar Kategori</h3>

                    {{-- Wrapper untuk tabel agar responsif --}}
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-full text-left align-middle">
                            <thead class="border-b border-gray-200 dark:border-gray-700">
                                <tr class="text-xs font-medium text-gray-500 uppercase dark:text-gray-400">
                                    <th class="px-4 py-3">No.</th>
                                    <th class="px-4 py-3">Nama Kategori</th>
                                    <th class="px-4 py-3 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse ($categories as $category)
                                    <tr wire:key="{{ $category->id }}" class="text-sm text-gray-900 dark:text-white">
                                        <td class="px-4 py-3">
                                            {{ ($categories->currentPage() - 1) * $categories->perPage() + $loop->iteration }}
                                        </td>
                                        <td class="px-4 py-3">{{ $category->nama_kategori }}</td>
                                        <td class="px-4 py-3 whitespace-nowrap text-right font-medium space-x-2">
                                            {{-- Tombol Edit --}}
                                            <button type="button" wire:click="edit({{ $category->id }})"
                                                    class="px-3 py-1 bg-yellow-500 text-white text-xs font-medium rounded-md hover:bg-yellow-600">
                                                Edit
                                            </button>

                                            {{-- Tombol Hapus --}}
                                            <button
                                                type="button"
                                                wire:click="delete({{ $category->id }})"
                                                wire:confirm="Anda yakin ingin menghapus '{{ $category->nama_kategori }}'? Ini tidak bisa dibatalkan."
                                                class="px-3 py-1 bg-red-600 text-white text-xs font-medium rounded-md hover:bg-red-700">
                                                Hapus
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="px-4 py-4 text-center text-sm text-gray-500 dark:text-gray-400">
                                            Belum ada data kategori.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Link Paginasi diletakkan di dalam card --}}
                    <div class="mt-6">
                        {{ $categories->links() }}
                    </div>
                </div>
            </div>

        </div>
    </main>
</div>
