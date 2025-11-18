<?php

use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;
use Livewire\Volt\Volt;

// welcome page publik
Volt::route('/', 'home')->name('home');

Route::get('dashboard', function () {
    $role = auth()->user()->role;

    return match ($role) {
        'admin' => redirect()->route('admin.dashboard'),
        'user'  => redirect()->route('user.dashboard'),
        default => redirect()->route('home'),
    };
})->middleware(['auth', 'verified'])->name('dashboard');

//Admin
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Volt::route('dashboard', 'admin.dashboard')->name('dashboard');

    Volt::route('/members', 'admin.member')
        ->name('member');

    Volt::route('/categories', 'admin.category')
        ->name('category');

    Volt::route('/books', 'admin.buku')
        ->name('buku');

    Volt::route('/peminjamans', 'admin.peminjaman')
        ->name('peminjaman');

    Volt::route('/monitoring-stok', 'admin.stock-monitoring')
        ->name('stock-monitoring');

    Volt::route('/reviews', 'admin.review')
        ->name('review');
});

//User
Route::middleware(['auth', 'user'])->prefix('user')->name('user.')->group(function () {

    Volt::route('dashboard', 'user.dashboard')->name('dashboard');

    Volt::route('koleksi', 'user.koleksi')->name('koleksi');

    Volt::route('rak', 'user.rak')->name('rak');
});

//All User
Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');
    Volt::route('settings/profile', 'settings.profile')->name('profile.edit'); // <-- Namanya sekarang benar
    Volt::route('settings/password', 'settings.password')->name('user-password.edit');
    Volt::route('settings/appearance', 'settings.appearance')->name('appearance.edit');
    Volt::route('settings/two-factor', 'settings.two-factor')
        ->middleware(
            when(
                Features::canManageTwoFactorAuthentication()
                    && Features::optionEnabled(Features::twoFactorAuthentication(), 'confirmPassword'),
                ['password.confirm'],
                [],
            ),
        )
        ->name('two-factor.show');
});
