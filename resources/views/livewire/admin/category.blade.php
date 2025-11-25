<?php

use Livewire\Volt\Component;
use App\Models\Category;
use Livewire\WithPagination;
use Livewire\Attributes\Url;
use Illuminate\Validation\Rule;
use Illuminate\Database\QueryException;

new class extends Component
{
    use WithPagination;

    // --- Search & Filter ---
    #[Url]
    public $search = '';

    // --- Form Properties ---
    public string $nama_kategori = '';
    public ?Category $editingCategory = null;

    // --- Validation Rules ---
    protected function rules()
    {
        return [
            'nama_kategori' => [
                'required',
                'string',
                'max:100',
                Rule::unique('categories')->ignore($this->editingCategory?->id),
            ],
        ];
    }

    protected $messages = [
        'nama_kategori.required' => 'Nama kategori wajib diisi.',
        'nama_kategori.unique' => 'Nama kategori ini sudah ada.',
    ];

    // --- Data Query ---
    public function with(): array
    {
        $query = Category::orderBy('nama_kategori', 'asc');

        if ($this->search) {
            $query->where('nama_kategori', 'like', '%' . $this->search . '%');
        }

        return [
            'categories' => $query->paginate(10),
            'total_categories' => Category::count(),
        ];
    }

    // Reset pagination saat search berubah
    public function updatedSearch()
    {
        $this->resetPage();
    }

    // --- Actions ---
    public function save()
    {
        $validated = $this->validate();

        try {
            if ($this->editingCategory) {
                $this->editingCategory->update($validated);
                session()->flash('success', 'Kategori berhasil diperbarui.');
            } else {
                Category::create($validated);
                session()->flash('success', 'Kategori berhasil ditambahkan.');
            }
            $this->resetForm();
        } catch (QueryException $e) {
            session()->flash('error', 'Gagal menyimpan. Terjadi kesalahan database.');
        }
    }

    public function edit(Category $category)
    {
        $this->editingCategory = $category;
        $this->nama_kategori = $category->nama_kategori;
        $this->resetErrorBag();
    }

    public function delete(Category $category)
    {
        try {
            $nama = $category->nama_kategori;
            $category->delete();
            session()->flash('success', "Kategori \"$nama\" berhasil dihapus.");

            if ($this->editingCategory && $this->editingCategory->id === $category->id) {
                $this->resetForm();
            }
        } catch (QueryException $e) {
            session()->flash('error', 'Kategori tidak bisa dihapus karena masih digunakan oleh Buku.');
        }
    }

    public function cancelEdit()
    {
        $this->resetForm();
    }

    private function resetForm()
    {
        $this->reset('nama_kategori', 'editingCategory');
        $this->resetErrorBag();
    }
}; ?>

<div>
    <main class="flex-1 p-6 lg:p-10 bg-gray-50 dark:bg-gray-900 min-h-screen">

        {{-- HEADER --}}
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white tracking-tight">
                Manajemen Kategori
            </h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                Atur klasifikasi buku perpustakaan agar mudah ditemukan.
            </p>
        </div>

        {{-- FLASH MESSAGES (Fixed Position) --}}
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

        {{-- STATISTIK SIMPLE --}}
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4 mb-8">
            <div class="p-5 bg-white border border-gray-100 rounded-xl shadow-sm dark:bg-gray-800 dark:border-gray-700">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold text-gray-500 uppercase tracking-wide dark:text-gray-400">Total Kategori</p>
                        <p class="text-3xl font-bold text-gray-900 dark:text-white mt-1">{{ $total_categories }}</p>
                    </div>
                    <div class="p-3 bg-indigo-50 rounded-full dark:bg-indigo-900/20 text-indigo-600 dark:text-indigo-400">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path></svg>
                    </div>
                </div>
            </div>
        </div>

        {{-- MAIN LAYOUT --}}
        <div class="grid grid-cols-1 gap-8 lg:grid-cols-3 items-start">

            {{-- FORM COLUMN (STICKY) --}}
            <div class="lg:col-span-1 lg:sticky lg:top-6">
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 dark:bg-gray-800 dark:border-gray-700 overflow-hidden">
                    <div class="p-5 border-b border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                            @if($editingCategory)
                                <svg class="w-5 h-5 mr-2 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                Edit Kategori
                            @else
                                <svg class="w-5 h-5 mr-2 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                                Tambah Kategori
                            @endif
                        </h3>
                    </div>

                    <div class="p-6">
                        <form wire:submit="save" class="space-y-5">
                            <div>
                                <label for="nama_kategori" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nama Kategori</label>
                                <input type="text" id="nama_kategori" wire:model="nama_kategori"
                                    placeholder="Contoh: Fiksi Sains, Biografi..."
                                    class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white px-4 py-2.5 transition ease-in-out duration-150">
                                @error('nama_kategori') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            <div class="flex items-center gap-3 pt-2">
                                @if ($editingCategory)
                                    <button type="submit" class="flex-1 flex justify-center py-2.5 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition">
                                        Update
                                    </button>
                                    <button type="button" wire:click="cancelEdit" class="flex-1 flex justify-center py-2.5 px-4 border border-gray-300 rounded-lg shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600 dark:hover:bg-gray-600">
                                        Batal
                                    </button>
                                @else
                                    <button type="submit" class="w-full flex justify-center py-2.5 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition">
                                        <span wire:loading.remove wire:target="save">Simpan Kategori</span>
                                        <span wire:loading wire:target="save">Menyimpan...</span>
                                    </button>
                                @endif
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            {{-- TABLE COLUMN --}}
            <div class="lg:col-span-2">
                {{-- Toolbar --}}
                <div class="mb-4">
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </div>
                        <input wire:model.live.debounce.300ms="search" type="text" placeholder="Cari nama kategori..."
                            class="block w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg leading-5 bg-white placeholder-gray-500 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm dark:bg-gray-800 dark:border-gray-700 dark:text-white shadow-sm transition">
                    </div>
                </div>

                {{-- Table Card --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden dark:bg-gray-800 dark:border-gray-700 relative">

                    {{-- Loading Overlay --}}
                    <div wire:loading.flex wire:target="search, page, delete" class="absolute inset-0 bg-white/60 dark:bg-gray-900/60 z-10 items-center justify-center hidden backdrop-blur-[1px]">
                        <svg class="animate-spin h-8 w-8 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead class="bg-gray-50 dark:bg-gray-700/50">
                                <tr>
                                    <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider dark:text-gray-400 w-16 text-center">No.</th>
                                    <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider dark:text-gray-400">Nama Kategori</th>
                                    <th class="px-6 py-4 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider dark:text-gray-400 w-32">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse ($categories as $index => $category)
                                    <tr wire:key="cat-{{ $category->id }}" class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition group">
                                        <td class="px-6 py-4 text-center text-gray-500 text-sm">
                                            {{ $categories->firstItem() + $index }}
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0 h-9 w-9 bg-indigo-100 text-indigo-600 rounded-lg flex items-center justify-center text-sm font-bold mr-3 dark:bg-indigo-900/50 dark:text-indigo-300">
                                                    {{ substr($category->nama_kategori, 0, 1) }}
                                                </div>
                                                <span class="text-sm font-medium text-gray-900 dark:text-white">
                                                    {{ $category->nama_kategori }}
                                                </span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-right text-sm">
                                            <div class="flex justify-end gap-2 opacity-100 sm:opacity-0 sm:group-hover:opacity-100 transition-opacity">
                                                <button wire:click="edit({{ $category->id }})" class="text-indigo-600 hover:text-indigo-900 bg-indigo-50 hover:bg-indigo-100 p-1.5 rounded-md transition dark:bg-indigo-900/30 dark:hover:bg-indigo-900/50 dark:text-indigo-300" title="Edit">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                                </button>
                                                <button wire:click="delete({{ $category->id }})" wire:confirm="Yakin ingin menghapus kategori '{{ $category->nama_kategori }}'?" class="text-red-600 hover:text-red-900 bg-red-50 hover:bg-red-100 p-1.5 rounded-md transition dark:bg-red-900/30 dark:hover:bg-red-900/50 dark:text-red-300" title="Hapus">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="px-6 py-12 text-center">
                                            <div class="flex flex-col items-center">
                                                <div class="bg-gray-100 p-3 rounded-full dark:bg-gray-700/50 mb-3">
                                                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path></svg>
                                                </div>
                                                <h3 class="text-sm font-medium text-gray-900 dark:text-white">Tidak ada kategori ditemukan</h3>
                                                <p class="text-xs text-gray-500 mt-1 dark:text-gray-400">Coba kata kunci lain atau tambahkan kategori baru.</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="bg-gray-50 px-6 py-4 border-t border-gray-200 dark:bg-gray-800 dark:border-gray-700">
                        {{ $categories->links() }}
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>
