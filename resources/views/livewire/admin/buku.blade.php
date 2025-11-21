<?php

use Livewire\Volt\Component;
use App\Models\Book;
use App\Models\Category;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\QueryException;

new class extends Component
{
    use WithPagination;
    use WithFileUploads;

    // Properti Form
    public string $judul = '';
    public string $penulis = '';
    public string $deskripsi = '';
    public $category_id = '';
    public int $lisensi = 1;
    public string $slug = '';

    // Properti File Uploads
    public $file_ebook;
    public $gambar_cover;

    // Properti State Update
    public ?Book $editingBook = null;
    public $existing_file_ebook;
    public $existing_gambar_cover;

    protected function rules()
    {
        return [
            'category_id' => ['required', 'exists:categories,id'],
            'judul' => ['required', 'string', 'max:255'],
            'penulis' => ['nullable', 'string', 'max:255'],
            'deskripsi' => ['nullable', 'string', 'max:5000'],
            'lisensi' => ['required', 'integer', 'min:1'],
            'slug' => [
                'required', 'string', 'max:100',
                Rule::unique('books')->ignore($this->editingBook?->id),
            ],
            'file_ebook' => [
                $this->editingBook ? 'nullable' : 'required',
                'file', 'mimes:pdf,epub', 'max:20480', // Max 20MB
            ],
            'gambar_cover' => [
                'nullable', 'image', 'max:2048', // Max 2MB
            ],
        ];
    }

    public function updatedJudul($value)
    {
        if (!$this->editingBook || $this->editingBook->judul !== $value) {
            $this->slug = Str::slug($value);
        }
    }

    public function with(): array
    {
        return [
            'books' => Book::with('category')->orderBy('created_at', 'desc')->paginate(10),
            'categories' => Category::orderBy('nama_kategori', 'asc')->get(),
        ];
    }

    public function save()
    {
        $validated = $this->validate();
        $dataToSave = $validated;

        // Hapus key file dari array agar tidak error saat create/update massal
        unset($dataToSave['file_ebook']);
        unset($dataToSave['gambar_cover']);

        try {
            // --- 1. Handle File Ebook (PRIVATE STORAGE) ---
            if ($this->file_ebook) {
                // Jika sedang edit & ada file lama, hapus file lama dari Private Storage
                if ($this->editingBook && $this->editingBook->file_ebook) {
                    Storage::delete($this->editingBook->file_ebook); // <--- PERBAIKAN: Hapus dari local
                }

                // Simpan file baru ke Private Storage (storage/app/ebooks)
                // <--- PERBAIKAN: Hapus parameter 'public'
                $dataToSave['file_ebook'] = $this->file_ebook->store('ebooks');
            }

            // --- 2. Handle Gambar Cover (PUBLIC STORAGE) ---
            if ($this->gambar_cover) {
                // Jika sedang edit & ada cover lama, hapus cover lama dari Public Storage
                if ($this->editingBook && $this->editingBook->gambar_cover) {
                    Storage::disk('public')->delete($this->editingBook->gambar_cover);
                }

                // Simpan cover baru ke Public Storage (storage/app/public/covers)
                $dataToSave['gambar_cover'] = $this->gambar_cover->store('covers', 'public');
            }

            // --- Database Operation ---
            if ($this->editingBook) {
                $this->editingBook->update($dataToSave);
                session()->flash('success', 'Buku berhasil diperbarui.');
            } else {
                Book::create($dataToSave);
                session()->flash('success', 'Buku berhasil ditambahkan.');
            }

            $this->resetForm();

        } catch (QueryException $e) {
            session()->flash('error', 'Terjadi kesalahan database: ' . Str::limit($e->getMessage(), 100));
        } catch (\Exception $e) {
            session()->flash('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function edit(Book $book)
    {
        $this->editingBook = $book;
        $this->category_id = $book->category_id;
        $this->judul = $book->judul;
        $this->penulis = $book->penulis;
        $this->deskripsi = $book->deskripsi ?? '';
        $this->lisensi = $book->lisensi;
        $this->slug = $book->slug;

        $this->existing_file_ebook = $book->file_ebook;
        $this->existing_gambar_cover = $book->gambar_cover;

        $this->reset('file_ebook', 'gambar_cover');
        $this->resetErrorBag();
    }

    public function delete(Book $book)
    {
        try {
            $ebookPath = $book->file_ebook;
            $coverPath = $book->gambar_cover;

            $book->delete();

            // 1. Hapus Ebook dari Private Storage
            if ($ebookPath) {
                Storage::delete($ebookPath); // <--- PERBAIKAN: Default ke local disk
            }

            // 2. Hapus Cover dari Public Storage
            if ($coverPath) {
                Storage::disk('public')->delete($coverPath);
            }

            session()->flash('success', 'Buku berhasil dihapus.');

            if ($this->editingBook && $this->editingBook->id === $book->id) {
                $this->resetForm();
            }

        } catch (QueryException $e) {
            session()->flash('error', 'Buku tidak bisa dihapus karena sedang dipinjam atau data terkait masih ada.');
        }
    }

    public function cancelEdit()
    {
        $this->resetForm();
    }

    private function resetForm()
    {
        $this->reset('judul', 'penulis', 'deskripsi', 'category_id', 'lisensi', 'slug', 'file_ebook', 'gambar_cover', 'existing_file_ebook', 'existing_gambar_cover');
        $this->lisensi = 1;
        $this->editingBook = null;
        $this->resetErrorBag();
    }
}; ?>

<div>
    <main class="flex-1 p-6 lg:p-10">
        <header class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Manajemen Buku</h1>
            <p class="mt-1 text-gray-600 dark:text-gray-400">Tambah, edit, dan hapus buku di perpustakaan.</p>
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
                     x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" x-transition>
                    {{ session('error') }}
                </div>
            @endif
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">

            {{-- FORM INPUT --}}
            <div class="lg:col-span-1">
                <div class="p-6 bg-white rounded-lg shadow-md dark:bg-gray-800">
                    <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">
                        {{ $editingBook ? 'Edit Buku' : 'Tambah Buku Baru' }}
                    </h3>

                    <form wire:submit="save" class="space-y-4">
                        {{-- Judul --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Judul</label>
                            <input type="text" wire:model.live="judul" class="block w-full mt-1 px-3 py-2 border rounded-md shadow-sm dark:bg-gray-700 dark:text-white dark:border-gray-600 @error('judul') border-red-500 @enderror">
                            @error('judul') <span class="text-red-600 text-sm mt-1">{{ $message }}</span> @enderror
                        </div>

                        {{-- Slug --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Slug</label>
                            <input type="text" wire:model="slug" readonly class="block w-full mt-1 px-3 py-2 bg-gray-100 border rounded-md dark:bg-gray-900 dark:text-gray-400">
                        </div>

                        {{-- Penulis --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Penulis</label>
                            <input type="text" wire:model="penulis" class="block w-full mt-1 px-3 py-2 border rounded-md shadow-sm dark:bg-gray-700 dark:text-white dark:border-gray-600">
                            @error('penulis') <span class="text-red-600 text-sm mt-1">{{ $message }}</span> @enderror
                        </div>

                        {{-- Deskripsi --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Deskripsi</label>
                            <textarea wire:model="deskripsi" rows="3" class="block w-full mt-1 px-3 py-2 border rounded-md shadow-sm dark:bg-gray-700 dark:text-white dark:border-gray-600"></textarea>
                            @error('deskripsi') <span class="text-red-600 text-sm mt-1">{{ $message }}</span> @enderror
                        </div>

                        {{-- Kategori --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Kategori</label>
                            <select wire:model="category_id" class="block w-full mt-1 px-3 py-2 border rounded-md dark:bg-gray-700 dark:text-white dark:border-gray-600">
                                <option value="">Pilih Kategori</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->nama_kategori }}</option>
                                @endforeach
                            </select>
                            @error('category_id') <span class="text-red-600 text-sm mt-1">{{ $message }}</span> @enderror
                        </div>

                        {{-- Lisensi --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Lisensi (Stok)</label>
                            <input type="number" wire:model="lisensi" min="1" class="block w-full mt-1 px-3 py-2 border rounded-md dark:bg-gray-700 dark:text-white dark:border-gray-600">
                        </div>

                        {{-- File Ebook --}}
                        <div class="p-3 border border-indigo-200 rounded-lg bg-indigo-50 dark:bg-indigo-900/20 dark:border-indigo-800">
                            <label class="block text-sm font-bold text-indigo-700 dark:text-indigo-300 mb-1">File Ebook (PDF) <span class="text-xs font-normal text-gray-500">(Wajib Private)</span></label>
                            <input type="file" wire:model="file_ebook" class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-white dark:bg-gray-700 dark:text-gray-300">
                            <div wire:loading wire:target="file_ebook" class="text-xs text-blue-600 mt-1">Mengunggah...</div>
                            @if ($editingBook)
                                <p class="text-xs text-gray-500 mt-1">
                                    {{ $existing_file_ebook ? 'File ada: ' . basename($existing_file_ebook) : 'Belum ada file.' }}
                                </p>
                            @endif
                            @error('file_ebook') <span class="text-red-600 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>

                        {{-- Gambar Cover --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Gambar Cover</label>
                            <input type="file" wire:model="gambar_cover" accept="image/*" class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 dark:bg-gray-700 dark:text-gray-300">
                            <div wire:loading wire:target="gambar_cover" class="text-xs text-blue-600 mt-1">Mengunggah...</div>

                            @if ($gambar_cover)
                                <img src="{{ $gambar_cover->temporaryUrl() }}" class="mt-2 h-24 rounded border">
                            @elseif ($existing_gambar_cover)
                                <img src="{{ Storage::url($existing_gambar_cover) }}" class="mt-2 h-24 rounded border">
                            @endif
                            @error('gambar_cover') <span class="text-red-600 text-sm mt-1">{{ $message }}</span> @enderror
                        </div>

                        {{-- Tombol --}}
                        <div class="flex gap-2 pt-2">
                            @if ($editingBook)
                                <button type="submit" class="flex-1 bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">Update</button>
                                <button type="button" wire:click="cancelEdit" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-300">Batal</button>
                            @else
                                <button type="submit" class="w-full bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700">Simpan</button>
                            @endif
                        </div>
                        <div wire:loading wire:target="save" class="text-sm text-gray-500">Proses penyimpanan...</div>
                    </form>
                </div>
            </div>

            {{-- TABEL DATA --}}
            <div class="lg:col-span-2">
                <div class="bg-white rounded-lg shadow-md overflow-hidden dark:bg-gray-800">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">Buku</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">Info</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700">
                            @forelse ($books as $book)
                                <tr wire:key="{{ $book->id }}">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-16 w-12">
                                                @if ($book->gambar_cover)
                                                    <img class="h-16 w-12 rounded object-cover" src="{{ Storage::url($book->gambar_cover) }}" alt="">
                                                @else
                                                    <div class="h-16 w-12 bg-gray-200 flex items-center justify-center text-xs text-gray-500 rounded">No img</div>
                                                @endif
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900 dark:text-white">{{ Str::limit($book->judul, 30) }}</div>
                                                <div class="text-sm text-gray-500 dark:text-gray-400">{{ $book->penulis }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                            {{ $book->category->nama_kategori }}
                                        </span>
                                        <div class="text-xs text-gray-500 mt-1">Stok: {{ $book->lisensi }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <button wire:click="edit({{ $book->id }})" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300 mr-3">Edit</button>
                                        <button wire:click="delete({{ $book->id }})" wire:confirm="Hapus buku ini?" class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300">Hapus</button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">Belum ada buku.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                    <div class="p-4">
                        {{ $books->links() }}
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>
