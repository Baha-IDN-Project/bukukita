<?php

use function Livewire\Volt\with;
use function Livewire\Volt\layout; // <-- 1. TAMBAHKAN INI
use App\Models\Book;

// 2. TAMBAHKAN INI (memberitahu Volt untuk pakai layout public)
layout('components.layouts.public');

// Menggunakan 'with' helper dari Volt untuk mengambil data
with(fn () => [
    'books' => Book::with('category')->latest()->paginate(12)
]);

?>

<div>
    <div class="container mx-auto px-4 py-8">
        <h2 class="text-3xl font-bold text-gray-800 mb-6">📚 Katalog Buku</h2>

        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            {{-- Sisa kode Anda (grid buku, dll) tidak perlu diubah --}}
            @forelse ($books as $book)
                <div class="bg-white rounded-lg shadow-md overflow-hidden transition-transform duration-300 hover:scale-105">

                    <img class="h-56 w-full object-cover"
                         src="{{ $book->cover ? asset('storage/'. $book->cover) : 'https://via.placeholder.com/300x400.png?text=No+Cover' }}"
                         alt="Cover buku {{ $book->title }}">

                    <div class="p-4">
                        <span class="inline-block bg-indigo-100 text-indigo-700 text-xs font-semibold px-2 py-1 rounded-full mb-2">
                            {{ $book->category->nama_kategori ?? 'Tanpa Kategori' }}
                        </span>

                        <h3 class="font-bold text-lg text-gray-900 truncate" title="{{ $book->title }}">
                            {{ $book->title }}
                        </h3>

                        <p class="text-gray-600 text-sm">
                            oleh {{ $book->author }}
                        </p>

                        <a href="#" class="text-indigo-600 hover:text-indigo-800 text-sm font-medium mt-2 inline-block">
                            Lihat Detail &rarr;
                        </a>
                    </div>
                </div>
            @empty
                <div class="col-span-full text-center text-gray-500 py-10">
                    <p class="text-xl">Belum ada buku yang ditambahkan.</p>
                </div>
            @endforelse
        </div>

        <div class="mt-8">
            {{ $books->links() }}
        </div>

    </div>
</div>
