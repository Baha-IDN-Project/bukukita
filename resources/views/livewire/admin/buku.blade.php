<?php

use Livewire\Volt\Component;
use App\Models\Book;
use App\Models\Category;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Livewire\Attributes\Url;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\QueryException;

new class extends Component
{
    use WithPagination;
    use WithFileUploads;

    // --- Search & Filter ---
    #[Url]
    public $search = '';

    // --- Form Properties ---
    public string $judul = '';
    public string $penulis = '';
    public string $deskripsi = '';
    public array $selected_categories = []; // Array ID kategori
    public int $lisensi = 1;
    public string $slug = '';

    // --- File Uploads ---
    public $file_ebook;
    public $gambar_cover;

    // --- State ---
    public ?Book $editingBook = null;
    public $existing_file_ebook;
    public $existing_gambar_cover;

    // --- Rules ---
    protected function rules()
    {
        return [
            'selected_categories' => ['required', 'array', 'min:1'],
            'selected_categories.*' => ['exists:categories,id'],
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
            'gambar_cover' => ['nullable', 'image', 'max:2048'], // Max 2MB
        ];
    }

    protected $messages = [
        'selected_categories.required' => 'Pilih minimal satu kategori.',
        'file_ebook.required' => 'File Ebook wajib diunggah untuk buku baru.',
        'file_ebook.mimes' => 'Format ebook harus PDF atau EPUB.',
    ];

    // --- Lifecycle Hooks ---
    public function updatedJudul($value)
    {
        // Auto-generate slug jika tidak sedang mengedit judul buku yang sudah ada (opsional)
        // Atau biarkan user mengedit slug manual jika perlu
        if (!$this->editingBook || $this->editingBook->judul !== $value) {
            $this->slug = Str::slug($value);
        }
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    // --- Data Query ---
    public function with(): array
    {
        $query = Book::with('categories')->orderBy('created_at', 'desc');

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('judul', 'like', '%' . $this->search . '%')
                  ->orWhere('penulis', 'like', '%' . $this->search . '%');
            });
        }

        return [
            'books' => $query->paginate(10),
            'categories' => Category::orderBy('nama_kategori', 'asc')->get(),
            'total_books' => Book::count(),
        ];
    }

    // --- Actions ---
    public function save()
    {
        $validated = $this->validate();

        // Pisahkan data relasi dan file
        $categoryIds = $validated['selected_categories'];
        unset($validated['selected_categories'], $validated['file_ebook'], $validated['gambar_cover']);

        $dataToSave = $validated;

        try {
            // 1. Upload File Ebook
            if ($this->file_ebook) {
                if ($this->editingBook && $this->editingBook->file_ebook) {
                    Storage::disk('public')->delete($this->editingBook->file_ebook);
                }
                $dataToSave['file_ebook'] = $this->file_ebook->store('ebooks', 'public');
            }

            // 2. Upload Cover
            if ($this->gambar_cover) {
                if ($this->editingBook && $this->editingBook->gambar_cover) {
                    Storage::disk('public')->delete($this->editingBook->gambar_cover);
                }
                $dataToSave['gambar_cover'] = $this->gambar_cover->store('covers', 'public');
            }

            // 3. Simpan Database
            if ($this->editingBook) {
                $this->editingBook->update($dataToSave);
                $this->editingBook->categories()->sync($categoryIds);
                session()->flash('success', 'Buku berhasil diperbarui.');
            } else {
                $book = Book::create($dataToSave);
                $book->categories()->attach($categoryIds);
                session()->flash('success', 'Buku berhasil ditambahkan.');
            }

            $this->resetForm();

        } catch (\Exception $e) {
            session()->flash('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function edit(Book $book)
    {
        $this->editingBook = $book;
        $this->selected_categories = $book->categories()->pluck('categories.id')->toArray();

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
            $judul = $book->judul;

            $book->delete(); // Cascade delete relasi categories di DB level biasanya

            // Hapus file fisik
            if ($ebookPath && Storage::disk('public')->exists($ebookPath)) {
                Storage::disk('public')->delete($ebookPath);
            }
            if ($coverPath && Storage::disk('public')->exists($coverPath)) {
                Storage::disk('public')->delete($coverPath);
            }

            session()->flash('success', "Buku \"$judul\" berhasil dihapus.");

            if ($this->editingBook && $this->editingBook->id === $book->id) {
                $this->resetForm();
            }
        } catch (QueryException $e) {
            session()->flash('error', 'Gagal menghapus buku. Mungkin sedang dipinjam.');
        }
    }

    public function cancelEdit()
    {
        $this->resetForm();
    }

    private function resetForm()
    {
        $this->reset(
            'judul', 'penulis', 'deskripsi', 'selected_categories',
            'lisensi', 'slug', 'file_ebook', 'gambar_cover',
            'existing_file_ebook', 'existing_gambar_cover', 'editingBook'
        );
        $this->lisensi = 1;
        $this->resetErrorBag();
    }
}; ?>

<div>
    <main class="flex-1 p-6 lg:p-10 bg-gray-50 dark:bg-gray-900 min-h-screen">

        {{-- HEADER --}}
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white tracking-tight">
                Manajemen Buku
            </h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                Katalog buku digital, upload ebook, dan manajemen stok lisensi.
            </p>
        </div>

        {{-- FLASH MESSAGES --}}
        <div class="fixed top-5 right-5 z-50 w-full max-w-sm">
            @if (session('success'))
                <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)" x-transition
                    class="p-4 mb-2 bg-green-50 text-green-800 border-l-4 border-green-500 rounded shadow-lg flex items-center dark:bg-green-900 dark:text-green-100">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    {{ session('success') }}
                </div>
            @endif
            @if (session('error'))
                <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)" x-transition
                    class="p-4 mb-2 bg-red-50 text-red-800 border-l-4 border-red-500 rounded shadow-lg flex items-center dark:bg-red-900 dark:text-red-100">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    {{ session('error') }}
                </div>
            @endif
        </div>

        {{-- STATISTIK --}}
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4 mb-8">
            <div class="p-5 bg-white border border-gray-100 rounded-xl shadow-sm dark:bg-gray-800 dark:border-gray-700">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold text-gray-500 uppercase tracking-wide dark:text-gray-400">Total Judul Buku</p>
                        <p class="text-3xl font-bold text-gray-900 dark:text-white mt-1">{{ $total_books }}</p>
                    </div>
                    <div class="p-3 bg-purple-50 rounded-full dark:bg-purple-900/20 text-purple-600 dark:text-purple-400">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                    </div>
                </div>
            </div>
        </div>

        {{-- MAIN CONTENT --}}
        <div class="grid grid-cols-1 gap-8 lg:grid-cols-3 items-start">

            {{-- FORM COLUMN (STICKY) --}}
            <div class="lg:col-span-1 lg:sticky lg:top-6">
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 dark:bg-gray-800 dark:border-gray-700 overflow-hidden">
                    <div class="p-5 border-b border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                            @if($editingBook)
                                <svg class="w-5 h-5 mr-2 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                Edit Buku
                            @else
                                <svg class="w-5 h-5 mr-2 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                Tambah Buku
                            @endif
                        </h3>
                    </div>

                    <div class="p-6">
                        <form wire:submit="save" class="space-y-4">

                            {{-- Judul --}}
                            <div>
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Judul Buku</label>
                                <input type="text" wire:model.live="judul" placeholder="Judul Lengkap" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                @error('judul') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                            </div>

                            {{-- Penulis & Slug --}}
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Penulis</label>
                                    <input type="text" wire:model="penulis" placeholder="Nama Penulis" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Slug (Auto)</label>
                                    <input type="text" wire:model="slug" readonly class="block w-full rounded-lg border-gray-300 bg-gray-100 text-gray-500 text-sm dark:bg-gray-900 dark:border-gray-700">
                                </div>
                            </div>

                            {{-- Kategori --}}
                            <div>
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Kategori (Pilih minimal 1)</label>
                                <div class="border border-gray-300 rounded-lg p-3 max-h-40 overflow-y-auto bg-gray-50 dark:bg-gray-700 dark:border-gray-600">
                                    <div class="grid grid-cols-2 gap-2">
                                        @foreach($categories as $category)
                                            <label class="inline-flex items-center space-x-2 cursor-pointer">
                                                <input type="checkbox" wire:model="selected_categories" value="{{ $category->id }}" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-800 dark:border-gray-500">
                                                <span class="text-xs text-gray-700 dark:text-gray-300">{{ $category->nama_kategori }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                                @error('selected_categories') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                            </div>

                            {{-- Deskripsi & Lisensi --}}
                            <div>
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Deskripsi Singkat</label>
                                <textarea wire:model="deskripsi" rows="3" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white"></textarea>
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Jumlah Lisensi (Stok)</label>
                                <input type="number" wire:model="lisensi" min="1" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                <p class="text-[10px] text-gray-500 mt-0.5">Jumlah kopi digital yang bisa dipinjam bersamaan.</p>
                            </div>

                            <hr class="border-gray-200 dark:border-gray-700 my-2">

                            {{-- File Uploads --}}
                            <div>
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">File Ebook (PDF/EPUB)</label>
                                <input type="file" wire:model="file_ebook" class="block w-full text-xs text-gray-500 file:mr-2 file:py-1 file:px-2 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 dark:file:bg-indigo-900/30 dark:file:text-indigo-300">
                                @if($existing_file_ebook && !$file_ebook)
                                    <p class="text-[10px] text-green-600 mt-1 flex items-center">
                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                        File saat ini tersedia.
                                    </p>
                                @endif
                                @error('file_ebook') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Cover Buku (Gambar)</label>
                                <input type="file" wire:model="gambar_cover" accept="image/*" class="block w-full text-xs text-gray-500 file:mr-2 file:py-1 file:px-2 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 dark:file:bg-indigo-900/30 dark:file:text-indigo-300">

                                {{-- Preview --}}
                                @if ($gambar_cover)
                                    <div class="mt-2">
                                        <img src="{{ $gambar_cover->temporaryUrl() }}" class="h-20 w-auto rounded border border-gray-200 shadow-sm object-cover">
                                    </div>
                                @elseif ($existing_gambar_cover)
                                    <div class="mt-2">
                                        <img src="{{ Storage::url($existing_gambar_cover) }}" class="h-20 w-auto rounded border border-gray-200 shadow-sm object-cover">
                                    </div>
                                @endif
                                @error('gambar_cover') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                            </div>

                            {{-- Actions --}}
                            <div class="flex items-center gap-3 pt-2">
                                <button type="submit" class="flex-1 flex justify-center py-2 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition disabled:opacity-50" wire:loading.attr="disabled" wire:target="save, file_ebook, gambar_cover">
                                    <span wire:loading.remove wire:target="save">
                                        {{ $editingBook ? 'Simpan Perubahan' : 'Tambah Buku' }}
                                    </span>
                                    <span wire:loading wire:target="save">Menyimpan...</span>
                                </button>

                                @if($editingBook)
                                    <button type="button" wire:click="cancelEdit" class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600">
                                        Batal
                                    </button>
                                @endif
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            {{-- TABLE COLUMN --}}
            <div class="lg:col-span-2">
                {{-- Search Bar --}}
                <div class="mb-4">
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </div>
                        <input wire:model.live.debounce.300ms="search" type="text" placeholder="Cari judul buku atau penulis..."
                            class="block w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg leading-5 bg-white placeholder-gray-500 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm dark:bg-gray-800 dark:border-gray-700 dark:text-white shadow-sm transition">
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden dark:bg-gray-800 dark:border-gray-700 relative">

                    {{-- Loading Overlay --}}
                    <div wire:loading.flex wire:target="search, page, delete" class="absolute inset-0 bg-white/60 dark:bg-gray-900/60 z-10 items-center justify-center hidden backdrop-blur-[1px]">
                        <svg class="animate-spin h-8 w-8 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead class="bg-gray-50 dark:bg-gray-700/50">
                                <tr>
                                    <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider dark:text-gray-400">Info Buku</th>
                                    <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider dark:text-gray-400">Kategori</th>
                                    <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider dark:text-gray-400 text-center">Lisensi</th>
                                    <th class="px-6 py-4 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider dark:text-gray-400">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse ($books as $book)
                                    <tr wire:key="book-{{ $book->id }}" class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition group">
                                        <td class="px-6 py-4">
                                            <div class="flex items-start space-x-4">
                                                <div class="flex-shrink-0 h-16 w-12 bg-gray-200 rounded overflow-hidden shadow-sm relative dark:bg-gray-700">
                                                    @if($book->gambar_cover)
                                                        <img src="{{ Storage::url($book->gambar_cover) }}" alt="{{ $book->judul }}" class="h-full w-full object-cover">
                                                    @else
                                                        <div class="flex items-center justify-center h-full text-gray-400">
                                                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                                        </div>
                                                    @endif
                                                </div>
                                                <div>
                                                    <div class="text-sm font-bold text-gray-900 dark:text-white line-clamp-1" title="{{ $book->judul }}">
                                                        {{ $book->judul }}
                                                    </div>
                                                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                                        {{ $book->penulis ?? 'Tanpa Penulis' }}
                                                    </div>
                                                    <div class="mt-1 flex items-center space-x-2">
                                                        @if($book->file_ebook)
                                                            <a href="{{ Storage::url($book->file_ebook) }}" target="_blank" class="text-[10px] bg-blue-50 text-blue-600 px-1.5 py-0.5 rounded hover:underline dark:bg-blue-900/30 dark:text-blue-300">
                                                                Lihat PDF
                                                            </a>
                                                        @else
                                                            <span class="text-[10px] bg-red-50 text-red-500 px-1.5 py-0.5 rounded dark:bg-red-900/30 dark:text-red-300">No File</span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </td>

                                        <td class="px-6 py-4">
                                            <div class="flex flex-wrap gap-1 max-w-[200px]">
                                                @forelse($book->categories as $cat)
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-indigo-50 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300">
                                                        {{ $cat->nama_kategori }}
                                                    </span>
                                                @empty
                                                    <span class="text-xs text-gray-400 italic">Uncategorized</span>
                                                @endforelse
                                            </div>
                                        </td>

                                        <td class="px-6 py-4 text-center">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                                                {{ $book->lisensi }} Kopi
                                            </span>
                                        </td>

                                        <td class="px-6 py-4 text-right text-sm font-medium">
                                            <div class="flex justify-end gap-2 opacity-100 sm:opacity-0 sm:group-hover:opacity-100 transition-opacity">
                                                <button wire:click="edit({{ $book->id }})" class="text-indigo-600 hover:text-indigo-900 bg-indigo-50 hover:bg-indigo-100 p-1.5 rounded-md transition dark:bg-indigo-900/30 dark:hover:bg-indigo-900/50 dark:text-indigo-300" title="Edit">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                                </button>
                                                <button wire:click="delete({{ $book->id }})" wire:confirm="Yakin ingin menghapus buku '{{ $book->judul }}'? File juga akan terhapus." class="text-red-600 hover:text-red-900 bg-red-50 hover:bg-red-100 p-1.5 rounded-md transition dark:bg-red-900/30 dark:hover:bg-red-900/50 dark:text-red-300" title="Hapus">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                                            <div class="flex flex-col items-center">
                                                <svg class="w-12 h-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                                                <p>Belum ada buku. Silakan tambahkan buku baru.</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="bg-gray-50 px-6 py-4 border-t border-gray-200 dark:bg-gray-800 dark:border-gray-700">
                        {{ $books->links() }}
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>
