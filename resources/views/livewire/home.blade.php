<?php

use Livewire\Volt\Component;
use function Livewire\Volt\layout;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title; // <-- 1. Import Atribut Title

layout('components.layouts.public');

?>

    <div class="flex h-full w-full flex-1 flex-col gap-8 rounded-xl">

        <!-- 1. Bagian Banner/Hero Utama -->
        <div class="relative aspect-[16/5] w-full overflow-hidden rounded-xl shadow-lg">
            <img src="https://placehold.co/1200x375/3498db/ffffff?text=Selamat+Datang+di+Bukukita"
                alt="Welcome Banner" class="absolute inset-0 size-full object-cover">
            <div class="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent"></div>
            <div class="absolute bottom-6 left-6 text-white">
                <h2 class="text-3xl font-bold">Koleksi Terbaru Bulan Ini</h2>
                <p class="mt-1 text-lg">Temukan buku-buku terbaik pilihan editor kami.</p>
            </div>
        </div>

        <!-- 2. Rak Buku (Diganti jadi "Buku Populer") -->
        <div>
            <div class="mb-4 flex items-center justify-between">
                {{-- 2. Judul diubah agar lebih cocok untuk publik --}}
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Buku Populer</h2>
                <a href="#"
                    class="text-sm font-medium text-blue-600 hover:text-blue-500 dark:text-blue-400 dark:hover:text-blue-300">
                    Lihat Semua
                </a>
            </div>

            <div class="flex space-x-4 overflow-x-auto pb-4">

                <!-- Contoh Kartu Buku 1 -->
                <div class="w-40 flex-shrink-0">
                    <div class="aspect-[3/4] overflow-hidden rounded-lg shadow-md">
                        <img src="https://placehold.co/300x400/f39c12/ffffff?text=Book+Cover+1"
                            alt="Book Cover 1" class="size-full object-cover transition-transform duration-300 hover:scale-105">
                    </div>
                    <h3 class="mt-2 truncate font-medium text-gray-900 dark:text-white"
                        title="Judul Buku yang Sangat Panjang Sekali Hingga Tidak Muat">Atomic Habits</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">James Clear</p>
                </div>

                <!-- Contoh Kartu Buku 2 -->
                <div class="w-40 flex-shrink-0">
                    <div class="aspect-[3/4] overflow-hidden rounded-lg shadow-md">
                        <img src="https://placehold.co/300x400/e74c3c/ffffff?text=Book+Cover+2"
                            alt="Book Cover 2" class="size-full object-cover transition-transform duration-300 hover:scale-105">
                    </div>
                    <h3 class="mt-2 truncate font-medium text-gray-900 dark:text-white">Filosofi Teras</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Henry M.</p>
                </div>

                <!-- Contoh Kartu Buku 3 -->
                <div class="w-40 flex-shrink-0">
                    <div class="aspect-[3/4] overflow-hidden rounded-lg shadow-md">
                        <img src="https://placehold.co/300x400/2ecc71/ffffff?text=Book+Cover+3"
                            alt="Book Cover 3" class="size-full object-cover transition-transform duration-300 hover:scale-105">
                    </div>
                    <h3 class="mt-2 truncate font-medium text-gray-900 dark:text-white">Gadis Kretek</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Ratih Kumala</p>
                </div>

                <!-- Placeholder: "Lihat Lainnya" -->
                <div class="w-40 flex-shrink-0">
                    <div
                        class="flex aspect-[3/4] items-center justify-center rounded-lg border-2 border-dashed border-neutral-300 dark:border-neutral-600">
                        <a href="#" class="text-center text-sm text-gray-500 hover:text-blue-500">
                            Lihat<br>Semua Buku
                        </a>
                    </div>
                </div>

            </div>
        </div>

        <!-- 3. Rak Koleksi Pilihan (Mirip dengan di atas) -->
        <div>
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Koleksi Pilihan Editor</h2>
                <a href="#"
                    class="text-sm font-medium text-blue-600 hover:text-blue-500 dark:text-blue-400 dark:hover:text-blue-300">
                    Lihat Semua
                </a>
            </div>
            <div class="flex space-x-4 overflow-x-auto pb-4">

                <!-- Contoh Kartu Buku 4 -->
                <div class="w-40 flex-shrink-0">
                    <div class="aspect-[3/4] overflow-hidden rounded-lg shadow-md">
                        <img src="https://placehold.co/300x400/9b59b6/ffffff?text=Book+Cover+4"
                            alt="Book Cover 4" class="size-full object-cover transition-transform duration-300 hover:scale-105">
                    </div>
                    <h3 class="mt-2 truncate font-medium text-gray-900 dark:text-white">Sapiens</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Yuval N. Harari</p>
                </div>

                <!-- Contoh Kartu Buku 5 -->
                <div class="w-40 flex-shrink-0">
                    <div class="aspect-[3/4] overflow-hidden rounded-lg shadow-md">
                        <img src="https://placehold.co/300x400/1abc9c/ffffff?text=Book+Cover+5"
                            alt="Book Cover 5" class="size-full object-cover transition-transform duration-300 hover:scale-105">
                    </div>
                    <h3 class="mt-2 truncate font-medium text-gray-900 dark:text-white">Laskar Pelangi</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Andrea Hirata</p>
                </div>

                <!-- Contoh Kartu Buku 6 -->
                <div class="w-40 flex-shrink-0">
                    <div class="aspect-[3/4] overflow-hidden rounded-lg shadow-md">
                        <img src="https://placehold.co/300x400/34495e/ffffff?text=Book+Cover+6"
                            alt="Book Cover 6" class="size-full object-cover transition-transform duration-300 hover:scale-105">
                    </div>
                    <h3 class="mt-2 truncate font-medium text-gray-900 dark:text-white">Bumi Manusia</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Pramoedya A. T.</p>
                </div>
            </div>
        </div>

        <!-- 4. Rak Kategori (Contoh lain) -->
        <div>
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Kategori Populer</h2>
            </div>
            <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6">

                <!-- Kategori 1 -->
                <a href="#"
                    class="relative aspect-video overflow-hidden rounded-lg shadow-md transition-transform duration-300 hover:scale-105">
                    <img src="https://placehold.co/300x200/e67e22/ffffff?text=Fiksi+Ilmiah" alt="Kategori"
                        class="size-full object-cover">
                    <div class="absolute inset-0 bg-black/40"></div>
                    <h3 class="absolute bottom-2 left-2 font-semibold text-white">Fiksi Ilmiah</h3>
                </a>
                <!-- Kategori 2 -->
                <a href="#"
                    class="relative aspect-video overflow-hidden rounded-lg shadow-md transition-transform duration-300 hover:scale-105">
                    <img src="https://placehold.co/300x200/2980b9/ffffff?text=Bisnis" alt="Kategori"
                        class="size-full object-cover">
                    <div class="absolute inset-0 bg-black/40"></div>
                    <h3 class="absolute bottom-2 left-2 font-semibold text-white">Bisnis</h3>
                </a>
                <!-- Kategori 3 -->
                <a href="#"
                    class="relative aspect-video overflow-hidden rounded-lg shadow-md transition-transform duration-300 hover:scale-105">
                    <img src="https://placehold.co/300x200/c0392b/ffffff?text=Sejarah" alt="Kategori"
                        class="size-full object-cover">
                    <div class="absolute inset-0 bg-black/40"></div>
                    <h3 class="absolute bottom-2 left-2 font-semibold text-white">Sejarah</h3>
                </a>
                <!-- Kategori 4 -->
                <a href="#"
                    class="relative aspect-video overflow-hidden rounded-lg shadow-md transition-transform duration-300 hover:scale-105">
                    <img src="https://placehold.co/300x200/8e44ad/ffffff?text=Romansa" alt="Kategori"
                        class="size-full object-cover">
                    <div class="absolute inset-0 bg-black/40"></div>
                    <h3 class="absolute bottom-2 left-2 font-semibold text-white">Romansa</h3>
                </a>
            </div>
        </div>

    </div>

