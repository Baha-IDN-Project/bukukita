<?php
use Livewire\Volt\Component;
use Livewire\WithPagination;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

new class extends Component {
    use WithPagination;

    // Fungsi 'with' akan otomatis dipanggil
    // untuk mengambil data yang dibutuhkan oleh view
    public function with(): array
    {
        return [
            // Ambil data user, urutkan, dan paginasi
            'users' => User::orderBy('name', 'asc')->paginate(10),
        ];
    }
}; ?>

{{--
  Wrapper utama dengan padding, background putih, sudut membulat, dan shadow.
  Ini memberikan tampilan "card" yang modern.
--}}
<div class="bg-white dark:bg-gray-900 shadow-md rounded-lg p-6 md:p-8">

    {{-- Judul Halaman dan Tombol Create --}}
    {{-- Menggunakan flexbox untuk mensejajarkan judul dan tombol --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-4 sm:mb-0">
            Manajemen User
        </h1>

        {{-- Tombol "Tambah User" dengan styling primer (biru) --}}
        <a href="{{ route('admin.members.create') }}" wire:navigate
            class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-lg font-semibold text-sm text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:bg-blue-500 dark:hover:bg-blue-600 dark:focus:ring-offset-gray-900 transition ease-in-out duration-150">
            {{-- Ikon plus sederhana untuk visual --}}
            <svg class="w-4 h-4 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
            Tambah User Baru
        </a>
    </div>

    {{-- Wrapper tabel agar responsif (bisa di-scroll horizontal di layar kecil) --}}
    <div class="overflow-x-auto relative shadow-sm rounded-lg border border-gray-200 dark:border-gray-700">
        {{-- Tabel untuk menampilkan data --}}
        <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
            {{-- Header Tabel --}}
            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                <tr>
                    <th scope="col" class="px-6 py-3">Nama</th>
                    <th scope="col" class="px-6 py-3">Email</th>
                    <th scope="col" class="px-6 py-3">Bergabung</th>
                    <th scope="col" class="px-6 py-3">Aksi</th>
                </tr>
            </thead>

            {{-- Body Tabel --}}
            <tbody>
                @forelse ($users as $user)
                    {{-- Setiap baris data dengan border-bottom dan efek hover --}}
                    <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                        {{--
                          Menggunakan `th` dengan `scope="row"` untuk data utama di baris (Nama)
                          Ini baik untuk aksesibilitas dan styling.
                        --}}
                        <th scope="row"
                            class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                            {{ $user->name }}
                        </th>
                        <td class="px-6 py-4">
                            {{ $user->email }}
                        </td>
                        <td class="px-6 py-4">
                            {{ $user->created_at->format('d M Y') }}
                        </td>
                        <td class="px-6 py-4">
                            {{-- Placeholder untuk tombol Aksi --}}
                            <a href="{{ route('admin.members.create') }}" wire:navigate class="font-medium text-blue-600 dark:text-blue-500 hover:underline">Edit</a>
                            <span class="mx-1 text-gray-300 dark:text-gray-600">|</span>
                            <a href="#" class="font-medium text-red-600 dark:text-red-500 hover:underline">Delete</a>
                        </td>
                    </tr>
                @empty
                    {{-- Tampilan jika data kosong --}}
                    <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                        <td colspan="4" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                            Tidak ada data user.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Link Pagination --}}
    {{-- Laravel pagination biasanya sudah ter-style oleh Tailwind jika di-setup dengan benar --}}
    <div class="mt-6">
        {{ $users->links() }}
    </div>

</div>
