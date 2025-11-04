<?php

use Livewire\Volt\Component;

new class extends Component {
    //
}; ?>

<main class="flex-1 p-6 lg:p-10">
    <header class="flex flex-col items-start justify-between gap-4 mb-8 sm:flex-row sm:items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                Manajemen Member
            </h1>
            <p class="mt-1 text-gray-600 dark:text-gray-400">
                Kelola data anggota yang terdaftar di perpustakaan Anda.
            </p>
        </div>

        <button
            type="button"
            wire:click="bukaModalTambahMember"
            class="flex items-center gap-2 px-4 py-2 font-medium text-white bg-blue-600 rounded-lg shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800">
            <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
            Tambah Anggota Baru
        </button>
    </header>

    <div class="p-6 bg-white rounded-lg shadow-md dark:bg-gray-800">

        <div class="flex flex-col items-center justify-between gap-4 mb-6 sm:flex-row">
            <div class="relative w-full sm:max-w-xs">
                <input
                    type="search"
                    wire:model.live.debounce.300ms="search"
                    placeholder="Cari anggota (nama, email, ID)..."
                    class="w-full pl-10 pr-4 py-2 text-gray-900 border border-gray-300 rounded-lg dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-blue-500 focus:border-blue-500">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3">
                    <svg class="w-5 h-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                    </svg>
                </span>
            </div>

            <select
                wire:model.live="filterStatus"
                class="w-full text-gray-900 border border-gray-300 rounded-lg sm:w-auto dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-blue-500 focus:border-blue-500">
                <option value="">Semua Status</option>
                <option value="aktif">Aktif</option>
                <option value="nonaktif">Nonaktif</option>
            </select>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full min-w-full text-left">
                <thead class="border-b dark:border-gray-700">
                    <tr class="text-sm text-gray-600 dark:text-gray-400">
                        <th class="py-3 pr-4">Nama</th>
                        <th class="py-3 px-4">Kontak</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4">Tgl. Bergabung</th>
                        <th class="py-3 pl-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y dark:divide-gray-700">

                    <tr class="text-gray-900 dark:text-white">
                        <td class="py-4 pr-4">
                            <div class="flex items-center gap-3">
                                <img class="object-cover w-10 h-10 rounded-full" src="https://ui-avatars.com/api/?name=Budi+Santoso" alt="Avatar">
                                <div>
                                    <div class="font-medium">Budi Santoso</div>
                                    <div class="text-sm text-gray-500">ID: M-001</div>
                                </div>
                            </div>
                        </td>
                        <td class="py-4 px-4 text-sm text-gray-500 dark:text-gray-400">budi.s@example.com</td>
                        <td class="py-4 px-4">
                            <span class="px-2 py-1 text-xs font-medium text-green-800 bg-green-100 rounded-full dark:bg-green-900 dark:text-green-200">
                                Aktif
                            </span>
                        </td>
                        <td class="py-4 px-4 text-sm text-gray-500 dark:text-gray-400">10 Okt 2024</td>
                        <td class="py-4 pl-4 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <button wire:click="editMember(1)" class="p-1 text-blue-600 rounded-md hover:text-blue-800 dark:hover:text-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-500">
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
                                <a href="#" class="p-1 text-gray-500 rounded-md hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-500">
                                    <span class="sr-only">Lihat Detail</span>
                                    <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.01 9.964 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.01-9.964-7.178z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                </a>
                            </div>
                        </td>
                    </tr>

                    <tr class="text-gray-900 dark:text-white">
                        <td class="py-4 pr-4">
                            <div class="flex items-center gap-3">
                                <img class="object-cover w-10 h-10 rounded-full" src="https://ui-avatars.com/api/?name=Ana+Maria" alt="Avatar">
                                <div>
                                    <div class="font-medium">Ana Maria</div>
                                    <div class="text-sm text-gray-500">ID: M-002</div>
                                </div>
                            </div>
                        </td>
                        <td class="py-4 px-4 text-sm text-gray-500 dark:text-gray-400">ana.m@example.com</td>
                        <td class="py-4 px-4">
                            <span class="px-2 py-1 text-xs font-medium text-gray-800 bg-gray-200 rounded-full dark:bg-gray-600 dark:text-gray-100">
                                Nonaktif
                            </span>
                        </td>
                        <td class="py-4 px-4 text-sm text-gray-500 dark:text-gray-400">05 Sep 2024</td>
                        <td class="py-4 pl-4 text-right">
                            <div class="flex items-center justify-end gap-2">
                                </div>
                        </td>
                    </tr>
                    </tbody>
            </table>
        </div>

        <div class="mt-6 border-t dark:border-gray-700">
            <div class="flex items-center justify-between px-2 py-4">
                <p class="text-sm text-gray-700 dark:text-gray-400">
                    Menampilkan <span class="font-medium">1</span> s/d <span class="font-medium">10</span> dari <span class="font-medium">97</span> hasil
                </p>
                <div class="flex gap-1">
                    <button class="px-3 py-1 text-sm rounded-md bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600">Previous</button>
                    <button class="px-3 py-1 text-sm rounded-md bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600">Next</button>
                </div>
            </div>
        </div>

    </div>
</main>
