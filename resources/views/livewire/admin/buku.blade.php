<?php

use Livewire\Volt\Component;

new class extends Component {
    //
}; ?>

<main class="flex-1 p-6 lg:p-10">
    <header class="flex flex-col items-start justify-between gap-4 mb-8 sm:flex-row sm:items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                Manajemen Buku
            </h1>
            <p class="mt-1 text-gray-600 dark:text-gray-400">
                Kelola koleksi buku yang ada di perpustakaan Anda.
            </p>
        </div>

        <button
            type="button"
            wire:click="bukaModalTambahBuku"
            class="flex items-center gap-2 px-4 py-2 font-medium text-white bg-blue-600 rounded-lg shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800">
            <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v6m3-3H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            Tambah Buku Baru
        </button>
    </header>

    <div class="p-6 bg-white rounded-lg shadow-md dark:bg-gray-800">

        <div class="grid grid-cols-1 gap-4 mb-6 sm:grid-cols-2 lg:grid-cols-3">
            <div class="relative lg:col-span-1">
                <input
                    type="search"
                    wire:model.live.debounce.300ms="search"
                    placeholder="Cari (judul, penulis, ISBN)..."
                    class="w-full pl-10 pr-4 py-2 text-gray-900 border border-gray-300 rounded-lg dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-blue-500 focus:border-blue-500">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3">
                    <svg class="w-5 h-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                    </svg>
                </span>
            </div>

            <select
                wire:model.live="filterKategori"
                class="w-full text-gray-900 border border-gray-300 rounded-lg dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-blue-500 focus:border-blue-500">
                <option value="">Semua Kategori</option>
                <option value="fiksi">Fiksi</option>
                <option value="non-fiksi">Non-Fiksi</option>
                <option value="sains">Sains</option>
                </select>

            <select
                wire:model.live="filterStatus"
                class="w-full text-gray-900 border border-gray-300 rounded-lg dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-blue-500 focus:border-blue-500">
                <option value="">Semua Status</option>
                <option value="tersedia">Tersedia</option>
                <option value="dipinjam">Dipinjam</option>
                <option value="habis">Stok Habis</option>
            </select>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full min-w-full text-left">
                <thead class="border-b dark:border-gray-700">
                    <tr class="text-sm text-gray-600 dark:text-gray-400">
                        <th class="py-3 pr-4">Judul Buku</th>
                        <th class="py-3 px-4">Kategori</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4">Stok</th>
                        <th class="py-3 pl-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y dark:divide-gray-700">

                    <tr class="text-gray-900 dark:text-white">
                        <td class="py-4 pr-4">
                            <div class="flex items-center gap-3">
                                <img class="object-cover w-10 h-14 rounded" src="https://via.placeholder.com/40x56" alt="Cover Buku">
                                <div>
                                    <div class="font-medium">Dasar-Dasar Laravel 12</div>
                                    <div class="text-sm text-gray-500 dark:text-gray-400">oleh John Doe</div>
                                    <div class="text-xs text-gray-400">ISBN: 978-1-23456-789-0</div>
                                </div>
                            </div>
                        </td>
                        <td class="py-4 px-4 text-sm text-gray-500 dark:text-gray-400">
                            Non-Fiksi
                        </td>
                        <td class="py-4 px-4">
                            <span class="px-2 py-1 text-xs font-medium text-green-800 bg-green-100 rounded-full dark:bg-green-900 dark:text-green-200">
                                Tersedia
                            </span>
                        </td>
                        <td class="py-4 px-4 text-sm font-medium">
                            5 / 5
                        </td>
                        <td class="py-4 pl-4 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <button wire:click="editBuku(1)" class="p-1 text-blue-600 rounded-md hover:text-blue-800 dark:hover:text-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <span class="sr-only">Edit</span>
                                    <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                      <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                                    </svg>
                                </button>
                                <button wire:click="confirmDelete(1)" class="p-1 text-red-600 rounded-md hover:text-red-800 dark:hover:text-red-400 focus:outline-none focus:ring-2 focus:ring-red-500">
                                    <span class="sr-only">Hapus</span>
                                    <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12.502 0a48.097 48.097 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.144-2.09-2.201L12 2.49M15.75 8.25h-7.5" />
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>

                    <tr class="text-gray-900 dark:text-white">
                        <td class="py-4 pr-4">
                            <div class="flex items-center gap-3">
                                <img class="object-cover w-10 h-14 rounded" src="https://via.placeholder.com/40x56" alt="Cover Buku">
                                <div>
                                    <div class="font-medium">Atomic Habits</div>
                                    <div class="text-sm text-gray-500 dark:text-gray-400">oleh James Clear</div>
                                    <div class="text-xs text-gray-400">ISBN: 978-0-73521-129-2</div>
                                </div>
                            </div>
                        </td>
                        <td class="py-4 px-4 text-sm text-gray-500 dark:text-gray-400">
                            Non-Fiksi
                        </td>
                        <td class="py-4 px-4">
                            <span class="px-2 py-1 text-xs font-medium text-yellow-800 bg-yellow-100 rounded-full dark:bg-yellow-900 dark:text-yellow-200">
                                Dipinjam
                            </span>
                        </td>
                        <td class="py-4 px-4 text-sm font-medium">
                            0 / 3
                        </td>
                        <td class="py-4 pl-4 text-right">
                             </td>
                    </tr>
                    </tbody>
            </table>
        </div>

        <div class="mt-6 border-t dark:border-gray-700">
            <div class="flex items-center justify-between px-2 py-4">
                <p class="text-sm text-gray-700 dark:text-gray-400">
                    Menampilkan <span class="font-medium">1</span> s/d <span class="font-medium">10</span> dari <span class="font-medium">125</span> hasil
                </p>
                <div class="flex gap-1">
                    <button class="px-3 py-1 text-sm rounded-md bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600">Previous</button>
                    <button class="px-3 py-1 text-sm rounded-md bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600">Next</button>
                </div>
            </div>
        </div>

    </div>
</main>
