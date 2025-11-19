<?php

use Livewire\Volt\Component;
use App\Models\User;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Database\QueryException;

// Komponen Volt
new class extends Component
{
    use WithPagination;

    // Properti untuk Form
    public string $name = ''; // Sesuai standar User model (user minta 'nama')
    public string $email = '';
    public string $role = 'member'; // Default role

    // Properti untuk Password
    public string $password = '';
    public string $password_confirmation = '';

    // Properti untuk State Update
    public ?User $editingUser = null;

    /**
     * Terapkan validasi.
     */
    protected function rules()
    {
        return [
            // Gunakan 'name' sesuai model User standar
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($this->editingUser?->id),
            ],
            'role' => ['required', 'string', Rule::in(['admin', 'member'])], // Asumsi 2 role ini

            // Password: Wajib saat Create, Opsional saat Update
            'password' => [
                $this->editingUser ? 'nullable' : 'required', // Opsional saat edit
                'string',
                'min:8',
                'confirmed', // Otomatis cek 'password_confirmation'
            ],
        ];
    }

    /**
     * Pesan validasi kustom (Opsional)
     */
    protected $messages = [
        'name.required' => 'Nama tidak boleh kosong.',
        'email.required' => 'Email tidak boleh kosong.',
        'email.unique' => 'Email ini sudah terdaftar.',
        'password.required' => 'Password tidak boleh kosong.',
        'password.min' => 'Password minimal 8 karakter.',
        'password.confirmed' => 'Konfirmasi password tidak cocok.',
    ];

    /**
     * Mengambil data untuk view.
     */
    public function with(): array
    {
        return [
            // Ambil semua user, KECUALI admin yang sedang login
            // (Agar admin tidak bisa menghapus/mengedit dirinya sendiri)
            'users' => User::where('id', '!=', auth()->id())
                           ->orderBy('name', 'asc')
                           ->paginate(10),
        ];
    }

    /**
     * Method untuk CREATE dan UPDATE.
     */
    public function save()
    {
        $validated = $this->validate();

        try {
            // Siapkan data utama
            $data = [
                'name' => $validated['name'],
                'email' => $validated['email'],
                'role' => $validated['role'],
            ];

            // --- Logika Password (PENTING) ---
            // Hanya update password JIKA diisi.
            // Jika kosong saat edit, password lama tetap aman.
            if (!empty($validated['password'])) {
                $data['password'] = Hash::make($validated['password']);
            }

            if ($this->editingUser) {
                // --- UPDATE ---
                $this->editingUser->update($data);
                session()->flash('success', 'Data member berhasil diperbarui.');
            } else {
                // --- CREATE ---
                // Pastikan password di-hash saat create
                // (Validasi 'required' sudah memastikan $validated['password'] ada)
                $data['password'] = Hash::make($validated['password']);
                User::create($data);
                session()->flash('success', 'Member baru berhasil ditambahkan.');
            }

            // Reset form setelah sukses
            $this->resetForm();

        } catch (QueryException $e) {
            session()->flash('error', 'Terjadi kesalahan database.');
        }
    }

    /**
     * (UPDATE) Menyiapkan form untuk mode edit.
     */
    public function edit(User $user)
    {
        $this->editingUser = $user;

        // Isi form dengan data yang ada
        $this->name = $user->name;
        $this->email = $user->email;
        $this->role = $user->role;

        // Kosongkan field password
        $this->reset('password', 'password_confirmation');
        $this->resetErrorBag();
    }

    /**
     * (DELETE) Menghapus member.
     */
    public function delete(User $user)
    {
        // Keamanan ekstra: cegah penghapusan diri sendiri
        if ($user->id === auth()->id()) {
            session()->flash('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
            return;
        }

        try {
            $userName = $user->name;
            $user->delete();
            session()->flash('success', 'Member "' . $userName . '" berhasil dihapus.');

            // Reset form jika yang dihapus adalah yang sedang diedit
            if ($this->editingUser && $this->editingUser->id === $user->id) {
                $this->resetForm();
            }

        } catch (QueryException $e) {
            session()->flash('error', 'Member tidak bisa dihapus, mungkin terkait data peminjaman.');
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
        $this->reset('name', 'email', 'role', 'password', 'password_confirmation');
        $this->role = 'member'; // Kembalikan ke default
        $this->editingUser = null;
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
                Manajemen Member
            </h1>
            <p class="mt-1 text-gray-600 dark:text-gray-400">
                Tambah, edit, dan hapus akun member perpustakaan.
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
                        <p class="text-sm font-medium text-gray-500 uppercase dark:text-gray-400">Total Member</p>
                        <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ $users->total() }}</p>
                    </div>
                    <span class="p-3 bg-green-100 rounded-full dark:bg-green-900">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-users-icon lucide-users"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><path d="M16 3.128a4 4 0 0 1 0 7.744"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><circle cx="9" cy="7" r="4"/></svg>
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
                        {{ $editingUser ? 'Edit Member' : 'Tambah Member Baru' }}
                    </h3>

                    {{-- Form --}}
                    <form wire:submit="save" class="space-y-4">

                        {{-- Nama --}}
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nama</label>
                            <input type="text" id="name" wire:model="name"
                                   class="block w-full mt-1 px-3 py-2 border border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('name') border-red-500 @enderror"
                                   placeholder="Nama Lengkap Member">
                            @error('name') <span class="text-red-600 text-sm mt-1">{{ $message }}</span> @enderror
                        </div>

                        {{-- Email --}}
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Email</label>
                            <input type="email" id="email" wire:model="email"
                                   class="block w-full mt-1 px-3 py-2 border border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('email') border-red-500 @enderror"
                                   placeholder="email@contoh.com">
                            @error('email') <span class="text-red-600 text-sm mt-1">{{ $message }}</span> @enderror
                        </div>

                        {{-- Role --}}
                        <div>
                            <label for="role" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Role</label>
                            <select id="role" wire:model="role"
                                    class="block w-full mt-1 px-3 py-2 border border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('role') border-red-500 @enderror">
                                <option value="member">Member</option>
                                <option value="admin">Admin</option>
                            </select>
                            @error('role') <span class="text-red-600 text-sm mt-1">{{ $message }}</span> @enderror
                        </div>

                        <hr class="dark:border-gray-700">

                        {{-- Password --}}
                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Password</label>
                            <input type="password" id="password" wire:model="password"
                                   class="block w-full mt-1 px-3 py-2 border border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('password') border-red-500 @enderror"
                                   placeholder="{{ $editingUser ? 'Isi untuk ganti password' : 'Minimal 8 karakter' }}">
                            @error('password') <span class="text-red-600 text-sm mt-1">{{ $message }}</span> @enderror
                        </div>

                        {{-- Konfirmasi Password --}}
                        <div>
                            <label for="password_confirmation" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Konfirmasi Password</label>
                            <input type="password" id="password_confirmation" wire:model="password_confirmation"
                                   class="block w-full mt-1 px-3 py-2 border border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                   placeholder="Ulangi password">
                        </div>


                        {{-- Tombol Aksi Form --}}
                        <div class="flex items-center space-x-3 pt-2">
                            @if ($editingUser)
                                <button type="submit"
                                        class="w-full px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800">
                                    Update Member
                                </button>
                                <button type="button" wire:click="cancelEdit"
                                        class="w-full px-4 py-2 bg-gray-200 text-gray-700 text-sm font-medium rounded-md dark:bg-gray-600 dark:text-gray-200 hover:bg-gray-300 dark:hover:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-offset-2 dark:focus:ring-offset-gray-800">
                                    Batal
                                </button>
                            @else
                                <button type="submit"
                                        class="w-full px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-md shadow-sm hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800">
                                    Simpan Member Baru
                                </button>
                            @endif
                        </div>

                        <div wire:loading wire:target="save">
                            <span class="text-sm text-gray-500 dark:text-gray-400">Menyimpan...</span>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Kolom Kanan: TABEL DAFTAR MEMBER --}}
            <div class="lg:col-span-2">
                <div class="p-6 bg-white rounded-lg shadow-md dark:bg-gray-800">
                    <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">Daftar Member & Admin</h3>

                    <div class="overflow-x-auto">
                        <table class="w-full min-w-full text-left align-middle">
                            <thead class="border-b border-gray-200 dark:border-gray-700">
                                <tr class="text-xs font-medium text-gray-500 uppercase dark:text-gray-400">
                                    <th class="px-4 py-3">No.</th>
                                    <th class="px-4 py-3">Nama</th>
                                    <th class="px-4 py-3">Email</th>
                                    <th class="px-4 py-3">Role</th>
                                    <th class="px-4 py-3 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse ($users as $user)
                                    <tr wire:key="{{ $user->id }}" class="text-sm text-gray-900 dark:text-white">
                                        {{-- Nomor urut --}}
                                        <td class="px-4 py-3">
                                            {{ ($users->currentPage() - 1) * $users->perPage() + $loop->iteration }}
                                        </td>
                                        <td class="px-4 py-3 font-medium">{{ $user->name }}</td>
                                        <td class="px-4 py-3 text-gray-700 dark:text-gray-300">{{ $user->email }}</td>
                                        <td class="px-4 py-3">
                                            @if ($user->role == 'admin')
                                                <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                                                    Admin
                                                </span>
                                            @else
                                                <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                                    Member
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-right font-medium space-x-2">
                                            <button type="button" wire:click="edit({{ $user->id }})"
                                                    class="px-3 py-1 bg-yellow-500 text-white text-xs font-medium rounded-md hover:bg-yellow-600">
                                                Edit
                                            </button>
                                            <button
                                                type="button"
                                                wire:click="delete({{ $user->id }})"
                                                wire:confirm="Anda yakin ingin menghapus '{{ $user->name }}'? Ini tidak bisa dibatalkan."
                                                class="px-3 py-1 bg-red-600 text-white text-xs font-medium rounded-md hover:bg-red-700">
                                                Hapus
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-4 py-4 text-center text-sm text-gray-500 dark:text-gray-400">
                                            Belum ada data member.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Link Paginasi --}}
                    <div class="mt-6">
                        {{ $users->links() }}
                    </div>
                </div>
            </div>

        </div>
    </main>
</div>
