<?php

use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;
use Livewire\Volt\Volt;
use App\Http\Controllers\EbookController; // Pastikan ini di-import

// Welcome page publik
Volt::route('/', 'home')->name('home');

// Dashboard Redirect Logic
Route::get('dashboard', function () {
    $role = auth()->user()->role;

    return match ($role) {
        'admin' => redirect()->route('admin.dashboard'),
        'member' => redirect()->route('user.dashboard'),
        default => redirect()->route('home'),
    };
})->middleware(['auth', 'verified'])->name('dashboard');

// --- ADMIN ROUTES ---
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Volt::route('dashboard', 'admin.dashboard')->name('dashboard');
    Volt::route('/members', 'admin.member')->name('member');
    Volt::route('/categories', 'admin.category')->name('category');
    Volt::route('/books', 'admin.buku')->name('buku');
    Volt::route('/peminjamans', 'admin.peminjaman')->name('peminjaman');
    Volt::route('/monitoring-stok', 'admin.stock-monitoring')->name('stock-monitoring');
    Volt::route('/reviews', 'admin.review')->name('review');
});

// --- MEMBER ROUTES ---
// Pastikan middleware 'member' sudah didaftarkan di kernel atau gunakan cek role manual
Route::middleware(['auth', 'verified'])->prefix('member')->name('user.')->group(function () {
    Volt::route('dashboard', 'user.dashboard')->name('dashboard');
    Volt::route('koleksi', 'user.koleksi')->name('koleksi');
    Volt::route('rak', 'user.rak')->name('rak');
    Volt::route('buku/{book:slug}', 'user.buku-detail')->name('buku.detail');

    // Halaman Baca (Reader Interface)
    Volt::route('baca/{book:slug}', 'user.read')->name('baca');
});

// --- SETTINGS & UTILITIES ---
Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');
    Volt::route('settings/profile', 'settings.profile')->name('profile.edit');
    Volt::route('settings/password', 'settings.password')->name('user-password.edit');
    Volt::route('settings/appearance', 'settings.appearance')->name('appearance.edit');

    // Route Streaming PDF (Dipanggil oleh PDF.js di halaman reader)
    Route::get('book/stream/{book:slug}', [EbookController::class, 'streamPDF'])->name('book.stream');
});
