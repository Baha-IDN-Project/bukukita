<?php
// Bagian PHP (Logika)
use Livewire\Volt\Component;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Rule;
use Illuminate\Validation\Rule as ValidationRule; // Kita butuh alias

new class extends Component {
    // Properti ini akan di-inject secara otomatis oleh Livewire
    // berkat route model binding
    public User $user;

    // Properti form
    #[Rule('required|string|max:255')]
    public string $name = '';

    #[Rule('required|email|max:255')]
    public string $email = '';

    // Password opsional saat update
    #[Rule('nullable|string|min:8')]
    public string $password = '';

    // Method mount() dipanggil saat komponen pertama kali di-load
    // Kita isi properti form dengan data user yang ada
    public function mount(User $user): void
    {
        $this->user = $user;
        $this->name = $user->name;
        $this->email = $user->email;
    }

    // Method untuk menghandle submit form update
    public function update(): void
    {
        // Validasi sedikit berbeda untuk 'unique' email
        $validated = $this->validate([
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                // Cek unik, TAPI abaikan ID user ini sendiri
                ValidationRule::unique('users')->ignore($this->user->id),
            ],
            'password' => 'nullable|string|min:8',
        ]);

        // Update data user
        $this->user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
        ]);

        // Hanya update password JIKA diisi
        if (!empty($validated['password'])) {
            $this->user->update([
                'password' => Hash::make($validated['password']),
            ]);
        }

        // Redirect kembali ke halaman index
        session()->flash('success', 'User berhasil diupdate.');
        $this->redirect(route('admin.member'), navigate: true);
    }
}; ?>

<div>
    <h1>Edit User: {{ $user->name }}</h1>

    <form wire:submit="update">
        <div style="margin-bottom: 15px;">
            <label for="name">Nama</label><br>
            <input type="text" id="name" wire:model="name">
            @error('name') <span style="color: red;">{{ $message }}</span> @enderror
        </div>

        <div style="margin-bottom: 15px;">
            <label for="email">Email</label><br>
            <input type="email" id="email" wire:model="email">
            @error('email') <span style="color: red;">{{ $message }}</span> @enderror
        </div>

        <div style="margin-bottom: 15px;">
            <label for="password">Password</label><br>
            <input type="password" id="password" wire:model="password"
                   placeholder="Kosongkan jika tidak ingin ganti">
            @error('password') <span style="color: red;">{{ $message }}</span> @enderror
        </div>

        <div>
            <button type="submit">Update</button>
            <a href="{{ route('admin.member') }}" wire:navigate>
                Batal
            </a>
        </div>
    </form>
</div>
