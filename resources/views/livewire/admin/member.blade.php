<?php

use Livewire\Volt\Component;
use App\Models\User;
use Livewire\WithPagination;
use Livewire\Attributes\Url;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Database\QueryException;

new class extends Component
{
    use WithPagination;

    // --- Search & Filter ---
    #[Url]
    public $search = '';

    #[Url]
    public $roleFilter = '';

    // --- Form Properties ---
    public string $name = '';
    public string $email = '';
    public string $role = 'member';
    public string $password = '';
    public string $password_confirmation = '';

    // --- State ---
    public ?User $editingUser = null;

    // --- Rules ---
    protected function rules()
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required', 'string', 'email', 'max:255',
                Rule::unique('users')->ignore($this->editingUser?->id),
            ],
            'role' => ['required', 'string', Rule::in(['admin', 'member'])],
            'password' => [
                $this->editingUser ? 'nullable' : 'required',
                'string', 'min:8', 'confirmed',
            ],
        ];
    }

    protected $messages = [
        'email.unique' => 'Email ini sudah terdaftar.',
        'password.confirmed' => 'Konfirmasi password tidak cocok.',
        'password.min' => 'Password minimal 8 karakter.',
    ];

    // --- Lifecycle Hooks ---
    public function updatedSearch() { $this->resetPage(); }
    public function updatedRoleFilter() { $this->resetPage(); }

    // --- Data Query ---
    public function with(): array
    {
        // Query dasar: jangan tampilkan diri sendiri (current logged in user)
        $query = User::where('id', '!=', auth()->id())->orderBy('created_at', 'desc');

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('email', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->roleFilter) {
            $query->where('role', $this->roleFilter);
        }

        return [
            'users' => $query->paginate(10),
            'total_members' => User::where('role', 'member')->count(),
            'total_admins' => User::where('role', 'admin')->count(),
        ];
    }

    // --- Actions ---
    public function save()
    {
        $validated = $this->validate();

        $data = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
        ];

        // Hash password hanya jika diisi
        if (!empty($validated['password'])) {
            $data['password'] = Hash::make($validated['password']);
        }

        try {
            if ($this->editingUser) {
                $this->editingUser->update($data);
                session()->flash('success', 'Data pengguna diperbarui.');
            } else {
                // Untuk user baru, password wajib ada (sudah divalidasi 'required')
                $data['password'] = Hash::make($validated['password']);
                User::create($data);
                session()->flash('success', 'Pengguna baru berhasil ditambahkan.');
            }

            $this->resetForm();
        } catch (QueryException $e) {
            session()->flash('error', 'Database Error: Gagal menyimpan data.');
        }
    }

    public function edit(User $user)
    {
        $this->editingUser = $user;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->role = $user->role;

        $this->reset('password', 'password_confirmation');
        $this->resetErrorBag();
    }

    public function delete(User $user)
    {
        if ($user->id === auth()->id()) {
            session()->flash('error', 'Tidak dapat menghapus akun sendiri.');
            return;
        }

        try {
            $nama = $user->name;
            $user->delete();
            session()->flash('success', "Pengguna \"$nama\" berhasil dihapus.");

            if ($this->editingUser && $this->editingUser->id === $user->id) {
                $this->resetForm();
            }
        } catch (QueryException $e) {
            session()->flash('error', 'Gagal menghapus. User mungkin memiliki data peminjaman.');
        }
    }

    public function cancelEdit()
    {
        $this->resetForm();
    }

    private function resetForm()
    {
        $this->reset('name', 'email', 'role', 'password', 'password_confirmation', 'editingUser');
        $this->role = 'member';
        $this->resetErrorBag();
    }
}; ?>

<div>
    <main class="flex-1 p-6 lg:p-10 bg-gray-50 dark:bg-gray-900 min-h-screen">

        {{-- HEADER --}}
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white tracking-tight">
                Manajemen Pengguna
            </h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                Kelola akun member dan administrator sistem perpustakaan.
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
                        <p class="text-xs font-bold text-gray-500 uppercase tracking-wide dark:text-gray-400">Total Member</p>
                        <p class="text-3xl font-bold text-gray-900 dark:text-white mt-1">{{ $total_members }}</p>
                    </div>
                    <div class="p-3 bg-blue-50 rounded-full dark:bg-blue-900/20 text-blue-600 dark:text-blue-400">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                    </div>
                </div>
            </div>

            <div class="p-5 bg-white border border-gray-100 rounded-xl shadow-sm dark:bg-gray-800 dark:border-gray-700">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold text-gray-500 uppercase tracking-wide dark:text-gray-400">Total Admin</p>
                        <p class="text-3xl font-bold text-gray-900 dark:text-white mt-1">{{ $total_admins }}</p>
                    </div>
                    <div class="p-3 bg-red-50 rounded-full dark:bg-red-900/20 text-red-600 dark:text-red-400">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
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
                            @if($editingUser)
                                <svg class="w-5 h-5 mr-2 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                Edit User
                            @else
                                <svg class="w-5 h-5 mr-2 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                                Tambah User
                            @endif
                        </h3>
                    </div>

                    <div class="p-6">
                        <form wire:submit="save" class="space-y-4">
                            <div>
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Nama Lengkap</label>
                                <input type="text" wire:model="name" placeholder="John Doe" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                @error('name') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Email Address</label>
                                <input type="email" wire:model="email" placeholder="john@example.com" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                @error('email') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Role / Peran</label>
                                <select wire:model="role" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                    <option value="member">Member</option>
                                    <option value="admin">Admin</option>
                                </select>
                                @error('role') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                            </div>

                            <div class="border-t border-gray-100 dark:border-gray-700 pt-3 mt-2">
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Password {{ $editingUser ? '(Opsional)' : '(Wajib)' }}
                                </label>
                                <input type="password" wire:model="password" placeholder="{{ $editingUser ? 'Kosongkan jika tidak ubah' : 'Min. 8 Karakter' }}" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white mb-2">
                                @error('password') <span class="text-red-500 text-xs block mb-2">{{ $message }}</span> @enderror

                                <input type="password" wire:model="password_confirmation" placeholder="Ulangi Password" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            </div>

                            <div class="flex items-center gap-3 pt-2">
                                <button type="submit" class="flex-1 flex justify-center py-2 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition">
                                    <span wire:loading.remove wire:target="save">
                                        {{ $editingUser ? 'Update User' : 'Simpan User' }}
                                    </span>
                                    <span wire:loading wire:target="save">Proses...</span>
                                </button>

                                @if($editingUser)
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
                {{-- Toolbar --}}
                <div class="flex flex-col sm:flex-row gap-4 mb-4">
                    <div class="relative flex-1">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </div>
                        <input wire:model.live.debounce.300ms="search" type="text" placeholder="Cari nama atau email..."
                            class="block w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg leading-5 bg-white placeholder-gray-500 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm dark:bg-gray-800 dark:border-gray-700 dark:text-white shadow-sm transition">
                    </div>
                    <div class="w-full sm:w-40">
                        <select wire:model.live="roleFilter" class="block w-full pl-3 pr-10 py-2.5 border border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-lg dark:bg-gray-800 dark:border-gray-700 dark:text-white">
                            <option value="">Semua Role</option>
                            <option value="member">Member</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden dark:bg-gray-800 dark:border-gray-700 relative">

                    {{-- Loading Overlay --}}
                    <div wire:loading.flex wire:target="search, roleFilter, page, delete" class="absolute inset-0 bg-white/60 dark:bg-gray-900/60 z-10 items-center justify-center hidden backdrop-blur-[1px]">
                        <svg class="animate-spin h-8 w-8 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead class="bg-gray-50 dark:bg-gray-700/50">
                                <tr>
                                    <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider dark:text-gray-400">Pengguna</th>
                                    <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider dark:text-gray-400">Role</th>
                                    <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider dark:text-gray-400">Bergabung</th>
                                    <th class="px-6 py-4 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider dark:text-gray-400">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse ($users as $user)
                                    <tr wire:key="user-{{ $user->id }}" class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition group">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0 h-10 w-10">
                                                    {{-- Avatar Initials --}}
                                                    <div class="h-10 w-10 rounded-full flex items-center justify-center text-sm font-bold
                                                        {{ $user->role == 'admin' ? 'bg-red-100 text-red-600 dark:bg-red-900/50 dark:text-red-300' : 'bg-indigo-100 text-indigo-600 dark:bg-indigo-900/50 dark:text-indigo-300' }}">
                                                        {{ substr($user->name, 0, 2) }}
                                                    </div>
                                                </div>
                                                <div class="ml-4">
                                                    <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $user->name }}</div>
                                                    <div class="text-xs text-gray-500 dark:text-gray-400">{{ $user->email }}</div>
                                                </div>
                                            </div>
                                        </td>

                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if ($user->role == 'admin')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300 border border-red-200 dark:border-red-800">
                                                    Admin
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300 border border-blue-200 dark:border-blue-800">
                                                    Member
                                                </span>
                                            @endif
                                        </td>

                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                            {{ $user->created_at->format('d M Y') }}
                                        </td>

                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <div class="flex justify-end gap-2 opacity-100 sm:opacity-0 sm:group-hover:opacity-100 transition-opacity">
                                                <button wire:click="edit({{ $user->id }})" class="text-indigo-600 hover:text-indigo-900 bg-indigo-50 hover:bg-indigo-100 p-1.5 rounded-md transition dark:bg-indigo-900/30 dark:hover:bg-indigo-900/50 dark:text-indigo-300" title="Edit">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                                </button>
                                                <button wire:click="delete({{ $user->id }})" wire:confirm="Yakin ingin menghapus pengguna '{{ $user->name }}'?" class="text-red-600 hover:text-red-900 bg-red-50 hover:bg-red-100 p-1.5 rounded-md transition dark:bg-red-900/30 dark:hover:bg-red-900/50 dark:text-red-300" title="Hapus">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                                            <div class="flex flex-col items-center">
                                                <svg class="w-12 h-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                                                <p>Tidak ada pengguna ditemukan.</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="bg-gray-50 px-6 py-4 border-t border-gray-200 dark:bg-gray-800 dark:border-gray-700">
                        {{ $users->links() }}
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>
