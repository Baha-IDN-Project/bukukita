<?php
// Bagian PHP (Logika)
use Livewire\Volt\Component;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Rule; // Untuk validasi

new class extends Component {
    // Properti untuk di-bind ke form
    // Atribut #[Rule] adalah cara Livewire 3 untuk validasi
    #[Rule('required|string|max:255')]
    public string $name = '';

    #[Rule('required|email|max:255|unique:users')]
    public string $email = '';

    #[Rule('required|string|min:8')]
    public string $password = '';

    // Method ini akan dipanggil saat form disubmit
    public function save(): void
    {
        // Jalankan validasi
        $validated = $this->validate();

        // Hash password sebelum disimpan
        $validated['password'] = Hash::make($validated['password']);

        // Buat user baru
        User::create($validated);

        // Redirect kembali ke halaman index dengan pesan sukses
        // 'navigate: true' penting untuk SPA-like navigation
        session()->flash('success', 'User berhasil dibuat.');
        $this->redirect(route('admin.member'), navigate: true);
    }
}; ?>

{{--
  Wrapper ini hanya untuk demo, agar card-nya terlihat bagus di layar.
  Anda bisa hapus div ini jika halaman Anda sudah punya layout.
--}}
<div class="bg-gray-100 dark:bg-gray-900 min-h-screen p-8">
    <div class="max-w-2xl mx-auto p-8 bg-white rounded-lg shadow-lg dark:bg-gray-800">

        <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-6">
            Tambah User Baru
        </h1>

        <form wire:submit="save">
            <div class="grid grid-cols-1 gap-6">

                {{-- Input Field untuk Nama --}}
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Nama
                    </label>
                    <input type="text" id="name" wire:model="name"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white">

                    @error('name')
                        <span class="text-red-600 text-sm mt-1">{{ $message }}</span>
                    @enderror
                </div>

                {{-- Input Field untuk Email --}}
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Email
                    </label>
                    <input type="email" id="email" wire:model="email"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white">

                    @error('email')
                        <span class="text-red-600 text-sm mt-1">{{ $message }}</span>
                    @enderror
                </div>

                {{-- Input Field untuk Password --}}
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Password
                    </label>
                    <input type="password" id="password" wire:model="password"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white">

                    @error('password')
                        <span class="text-red-600 text-sm mt-1">{{ $message }}</span>
                    @enderror
                </div>

            </div>

            <div class="mt-8 flex justify-end gap-4">

                {{-- Tombol Batal (Secondary Button) --}}
                <a href="{{ route('admin.member') }}" wire:navigate
                    class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-lg font-semibold text-sm text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-700 dark:focus:ring-offset-gray-900 transition ease-in-out duration-150">
                    Batal
                </a>

                {{-- Tombol Simpan (Primary Button) --}}
                <button type="submit"
                    class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-lg font-semibold text-sm text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:bg-blue-500 dark:hover:bg-blue-600 dark:focus:ring-offset-gray-900 transition ease-in-out duration-150">
                    Simpan
                </button>
            </div>

        </form>
    </div>

</div>
