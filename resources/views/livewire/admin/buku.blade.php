<?php

use Livewire\Volt\Component;
use App\Models\Book;
use App\Models\Category;
use Livewire\WithPagination;
use Livewire\WithFileUploads; // Penting untuk upload file
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\QueryException;

// Komponen Volt
new class extends Component
{
    use WithPagination;
    use WithFileUploads; // Mengaktifkan fitur file upload Livewire

    // Properti untuk Form
    public string $judul = '';
    public string $penulis = '';
    public $category_id = ''; // Akan diisi oleh <select>
    public int $lisensi = 1; // Sesuai default di migrasi
    public string $slug = '';

    // Properti untuk File Uploads
    public $file_ebook; // Menampung objek file sementara
    public $gambar_cover; // Menampung objek file sementara

    // Properti untuk State Update
    public ?Book $editingBook = null;
    public $existing_file_ebook; // Menampung path string file yang sudah ada
    public $existing_gambar_cover; // Menampung path string gambar yang sudah ada

    /**
     * Terapkan validasi.
     */
    protected function rules()
    {
        return [
            'category_id' => ['required', 'exists:categories,id'],
            'judul' => ['required', 'string', 'max:255'],
            'penulis' => ['nullable', 'string', 'max:255'],
            'lisensi' => ['required', 'integer', 'min:1'],
            'slug' => [
                'required',
                'string',
                'max:100',
                Rule::unique('books')->ignore($this->editingBook?->id),
            ],
            // Aturan validasi file
            'file_ebook' => [
                // 'required' hanya saat create, 'nullable' saat update
                $this->editingBook ? 'nullable' : 'required',
                'file',
                'mimes:pdf,epub', // Sesuaikan dengan format ebook Anda
                'max:20480', // max 20MB (sesuaikan)
            ],
            'gambar_cover' => [
                'nullable', // Cover boleh kosong
                'image',
                'max:2048', // max 2MB (sesuaikan)
            ],
        ];
    }

    /**
     * Method 'hook' ini dijalankan setiap kali properti 'judul' diperbarui.
     * Ini akan otomatis membuat slug secara reaktif.
     */
    public function updatedJudul($value)
    {
        $this->slug = Str::slug($value);
    }

    /**
     * Mengambil data untuk view.
     */
    public function with(): array
    {
        return [
            // Eager load relasi 'category' untuk ditampilkan di tabel
            'books' => Book::with('category')->orderBy('judul', 'asc')->paginate(10),
            // Mengambil semua kategori untuk dropdown
            'categories' => Category::orderBy('nama_kategori', 'asc')->get(),
        ];
    }

    /**
     * Method untuk CREATE dan UPDATE.
     */
    public function save()
    {
        // Validasi data
        $validated = $this->validate();

        try {
            // --- Handle File Uploads ---

            // 1. Handle File Ebook
            if ($this->file_ebook) {
                // Hapus file lama jika ada (saat update)
                if ($this->editingBook && $this->editingBook->file_ebook) {
                    Storage::disk('public')->delete($this->editingBook->file_ebook);
                }
                // Simpan file baru dan dapatkan path-nya
                $validated['file_ebook'] = $this->file_ebook->store('ebooks', 'public');
            }

            // 2. Handle Gambar Cover
            if ($this->gambar_cover) {
                // Hapus file lama jika ada (saat update)
                if ($this->editingBook && $this->editingBook->gambar_cover) {
                    Storage::disk('public')->delete($this->editingBook->gambar_cover);
                }
                // Simpan file baru dan dapatkan path-nya
                $validated['gambar_cover'] = $this->gambar_cover->store('covers', 'public');
            }

            // --- Database Operation ---
            if ($this->editingBook) {
                // --- UPDATE ---
                $this->editingBook->update($validated);
                session()->flash('success', 'Buku berhasil diperbarui.');
            } else {
                // --- CREATE ---
                Book::create($validated);
                session()->flash('success', 'Buku berhasil ditambahkan.');
            }

            // Reset form setelah sukses
            $this->resetForm();

        } catch (QueryException $e) {
            session()->flash('error', 'Terjadi kesalahan database. Pastikan slug unik.');
        } catch (\Exception $e) {
            session()->flash('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * (UPDATE) Menyiapkan form untuk mode edit.
     */
    public function edit(Book $book)
    {
        $this->editingBook = $book;

        // Isi form dengan data yang ada
        $this->category_id = $book->category_id;
        $this->judul = $book->judul;
        $this->penulis = $book->penulis;
        $this->lisensi = $book->lisensi;
        $this->slug = $book->slug;

        // Simpan path file yang sudah ada untuk ditampilkan
        $this->existing_file_ebook = $book->file_ebook;
        $this->existing_gambar_cover = $book->gambar_cover;

        // Reset properti file upload dan error
        $this->reset('file_ebook', 'gambar_cover');
        $this->resetErrorBag();
    }

    /**
     * (DELETE) Menghapus buku.
     */
    public function delete(Book $book)
    {
        try {
            // Simpan path file sebelum dihapus dari DB
            $ebookPath = $book->file_ebook;
            $coverPath = $book->gambar_cover;

            // 1. Hapus data dari Database
            $book->delete();

            // 2. Hapus file fisik dari storage
            if ($ebookPath) {
                Storage::disk('public')->delete($ebookPath);
            }
            if ($coverPath) {
                Storage::disk('public')->delete($coverPath);
            }

            session()->flash('success', 'Buku "' . $book->judul . '" berhasil dihapus.');

            // Reset form jika yang dihapus adalah yang sedang diedit
            if ($this->editingBook && $this->editingBook->id === $book->id) {
                $this->resetForm();
            }

        } catch (QueryException $e) {
            session()->flash('error', 'Buku tidak bisa dihapus, mungkin terkait dengan data peminjaman.');
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
     * Helper internal untuk mereset semua properti form.
     */
    private function resetForm()
    {
        $this->reset('judul', 'penulis', 'category_id', 'lisensi', 'slug', 'file_ebook', 'gambar_cover', 'existing_file_ebook', 'existing_gambar_cover');
        $this->lisensi = 1; // Kembalikan ke default
        $this->editingBook = null;
        $this->resetErrorBag();
    }
}; ?>

{{--
======================================================================
    BAGIAN VIEW (HTML/BLADE)
======================================================================
--}}
<div>
    <main class="flex-1 p-6 lg:p-10">
        {{-- HEADER --}}
        <header class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                Manajemen Buku
            </h1>
            <p class="mt-1 text-gray-600 dark:text-gray-400">
                Tambah, edit, dan hapus buku di perpustakaan.
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
                        <p class="text-sm font-medium text-gray-500 uppercase dark:text-gray-400">Total Buku</p>
                        <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ $books->total() }}</p>
                    </div>
                    <span class="p-3 bg-blue-100 rounded-full dark:bg-blue-900">
                        <svg class="w-6 h-6 text-blue-600 dark:text-blue-300" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.185 0 4.236.638 6 1.756 1.764-1.118 3.815-1.756 6-1.756 2.185 0 4.236.638 6 1.756V4.262c-.938-.332-1.948-.512-3-.512-2.185 0-4.236.638-6 1.756z" />
                        </svg>
                    </span>
                </div>
            </div>
        </div>

        {{-- AREA KONTEN UTAMA (FORM & TABEL) --}}
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">

            {{-- Kolom Kiri: FORM CREATE & UPDATE --}}
            <div class="lg:col-span-1">
                <div class="p-6 bg-white rounded-lg shadow-md dark:bg-gray-800">
                    <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">
                        {{ $editingBook ? 'Edit Buku' : 'Tambah Buku Baru' }}
                    </h3>

                    {{-- Form --}}
                    <form wire:submit="save" class="space-y-4">

                        {{-- Judul --}}
                        <div>
                            <label for="judul" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Judul</label>
                            <input type="text" id="judul" wire:model.live="judul"
                                   class="block w-full mt-1 px-3 py-2 border border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('judul') border-red-500 @enderror"
                                   placeholder="Judul Buku">
                            @error('judul') <span class="text-red-600 text-sm mt-1">{{ $message }}</span> @enderror
                        </div>

                        {{-- Slug (Readonly) --}}
                        <div>
                            <label for="slug" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Slug</label>
                            <input type="text" id="slug" wire:model="slug" readonly
                                   class="block w-full mt-1 px-3 py-2 border border-gray-300 rounded-md shadow-sm bg-gray-100 dark:bg-gray-900 dark:border-gray-600 dark:text-gray-400 sm:text-sm"
                                   placeholder="Akan terisi otomatis">
                            @error('slug') <span class="text-red-600 text-sm mt-1">{{ $message }}</span> @enderror
                        </div>

                        {{-- Penulis --}}
                        <div>
                            <label for="penulis" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Penulis</label>
                            <input type="text" id="penulis" wire:model="penulis"
                                   class="block w-full mt-1 px-3 py-2 border border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('penulis') border-red-500 @enderror"
                                   placeholder="Nama Penulis">
                            @error('penulis') <span class="text-red-600 text-sm mt-1">{{ $message }}</span> @enderror
                        </div>

                        {{-- Kategori --}}
                        <div>
                            <label for="category_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Kategori</label>
                            <select id="category_id" wire:model="category_id"
                                    class="block w-full mt-1 px-3 py-2 border border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('category_id') border-red-500 @enderror">
                                <option value="">Pilih Kategori</option>
                                @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->nama_kategori }}</option>
                                @endforeach
                            </select>
                            @error('category_id') <span class="text-red-600 text-sm mt-1">{{ $message }}</span> @enderror
                        </div>

                        {{-- Lisensi --}}
                        <div>
                            <label for="lisensi" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Lisensi (Jumlah Salinan)</label>
                            <input type="number" id="lisensi" wire:model="lisensi" min="1"
                                   class="block w-full mt-1 px-3 py-2 border border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('lisensi') border-red-500 @enderror">
                            @error('lisensi') <span class="text-red-600 text-sm mt-1">{{ $message }}</span> @enderror
                        </div>

                        {{-- File Ebook --}}
                        <div>
                            <label for="file_ebook" class="block text-sm font-medium text-gray-700 dark:text-gray-300">File Ebook (PDF/EPUB)</label>
                            <input type="file" id="file_ebook" wire:model="file_ebook"
                                   class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 dark:text-gray-400 focus:outline-none dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 @error('file_ebook') border-red-500 @enderror">
                            <div wire:loading wire:target="file_ebook" class="text-sm text-blue-600 mt-1">Mengunggah file...</div>
                            @if ($existing_file_ebook && !$file_ebook)
                                <p class="text-sm text-gray-500 mt-1">File saat ini: <span class="font-medium">{{ Str::limit($existing_file_ebook, 30) }}</span></p>
                            @endif
                            @error('file_ebook') <span class="text-red-600 text-sm mt-1">{{ $message }}</span> @enderror
                        </div>

                        {{-- Gambar Cover --}}
                        <div>
                            <label for="gambar_cover" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Gambar Cover (Opsional)</label>
                            <input type="file" id="gambar_cover" wire:model="gambar_cover" accept="image/*"
                                   class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 dark:text-gray-400 focus:outline-none dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 @error('gambar_cover') border-red-500 @enderror">
                            <div wire:loading wire:target="gambar_cover" class="text-sm text-blue-600 mt-1">Mengunggah gambar...</div>

                            {{-- Preview Gambar --}}
                            @if ($gambar_cover) {{-- Preview untuk upload baru --}}
                                <img src="{{ $gambar_cover->temporaryUrl() }}" alt="Preview Cover" class="mt-2 h-32 w-auto rounded">
                            @elseif ($existing_gambar_cover) {{-- Preview untuk gambar yang sudah ada --}}
                                <img src="{{ Storage::url($existing_gambar_cover) }}" alt="Cover" class="mt-2 h-32 w-auto rounded">
                            @endif
                            @error('gambar_cover') <span class="text-red-600 text-sm mt-1">{{ $message }}</span> @enderror
                        </div>


                        {{-- Tombol Aksi Form --}}
                        <div class="flex items-center space-x-3 pt-2">
                            @if ($editingBook)
                                <button type="submit"
                                        class="w-full px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800">
                                    Update Buku
                                </button>
                                <button type="button" wire:click="cancelEdit"
                                        class="w-full px-4 py-2 bg-gray-200 text-gray-700 text-sm font-medium rounded-md dark:bg-gray-600 dark:text-gray-200 hover:bg-gray-300 dark:hover:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-offset-2 dark:focus:ring-offset-gray-800">
                                    Batal
                                </button>
                            @else
                                <button type="submit"
                                        class="w-full px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-md shadow-sm hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800">
                                    Simpan Buku Baru
                                </button>
                            @endif
                        </div>

                        {{-- Indikator loading saat menyimpan --}}
                        <div wire:loading wire:target="save">
                            <span class="text-sm text-gray-500 dark:text-gray-400">Menyimpan data dan file...</span>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Kolom Kanan: TABEL DAFTAR BUKU --}}
            <div class="lg:col-span-2">
                <div class="p-6 bg-white rounded-lg shadow-md dark:bg-gray-800">
                    <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">Daftar Buku</h3>

                    <div class="overflow-x-auto">
                        <table class="w-full min-w-full text-left align-middle">
                            <thead class="border-b border-gray-200 dark:border-gray-700">
                                <tr class="text-xs font-medium text-gray-500 uppercase dark:text-gray-400">
                                    <th class="px-4 py-3">No.</th>
                                    <th class="px-4 py-3">Cover</th>
                                    <th class="px-4 py-3">Judul</th>
                                    <th class="px-4 py-3">Kategori</th>
                                    <th class="px-4 py-3">Lisensi</th>
                                    <th class="px-4 py-3 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse ($books as $book)
                                    <tr wire:key="{{ $book->id }}" class="text-sm text-gray-900 dark:text-white">
                                        {{-- Sesuai permintaan: "kecualikan id-nya" (pakai nomor urut) --}}
                                        <td class="px-4 py-3">
                                            {{ ($books->currentPage() - 1) * $books->perPage() + $loop->iteration }}
                                        </td>
                                        <td class="px-4 py-3">
                                            @if ($book->gambar_cover)
                                                <img src="{{ Storage::url($book->gambar_cover) }}" alt="Cover" class="h-16 w-12 object-cover rounded">
                                            @else
                                                <div class="h-16 w-12 bg-gray-200 dark:bg-gray-700 rounded flex items-center justify-center text-xs text-gray-500">No Cover</div>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="font-medium">{{ $book->judul }}</div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400">{{ $book->penulis ?? 'N/A' }}</div>
                                        </td>
                                        <td class="px-4 py-3">
                                            <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                                {{ $book->category->nama_kategori ?? 'N/A' }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3">{{ $book->lisensi }}</td>
                                        <td class="px-4 py-3 whitespace-nowrap text-right font-medium space-x-2">
                                            <button type="button" wire:click="edit({{ $book->id }})"
                                                    class="px-3 py-1 bg-yellow-500 text-white text-xs font-medium rounded-md hover:bg-yellow-600">
                                                Edit
                                            </button>
                                            <button
                                                type="button"
                                                wire:click="delete({{ $book->id }})"
                                                wire:confirm="Anda yakin ingin menghapus '{{ $book->judul }}'? Semua file terkait akan dihapus."
                                                class="px-3 py-1 bg-red-600 text-white text-xs font-medium rounded-md hover:bg-red-700">
                                                Hapus
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-4 py-4 text-center text-sm text-gray-500 dark:text-gray-400">
                                            Belum ada data buku.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Link Paginasi --}}
                    <div class="mt-6">
                        {{ $books->links() }}
                    </div>
                </div>
            </div>

        </div>
    </main>
</div>
