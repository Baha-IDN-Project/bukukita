<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Peminjaman;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class EbookController extends Controller
{
    public function streamPDF(Book $book)
    {
        // 1. Validasi Keamanan (Cek User Login & Status Peminjaman)
        $hasAccess = Peminjaman::where('user_id', Auth::id())
            ->where('book_id', $book->id)
            ->where('status', 'dipinjam')
            ->where(function ($query) {
                $query->whereNull('tanggal_harus_kembali')
                      ->orWhereDate('tanggal_harus_kembali', '>=', Carbon::today());
            })
            ->exists();

        // Bypass akses untuk Admin
        if (Auth::user()->role === 'admin') {
            $hasAccess = true;
        }

        if (!$hasAccess) {
            abort(403, 'Akses ditolak. Masa pinjam habis atau Anda belum meminjam buku ini.');
        }

        // 2. Tentukan Path File (PERBAIKAN DISINI: file_path -> file_ebook)
        $path = $book->file_ebook;

        // 3. Cek Keberadaan File di Storage (Disk Public)
        if (!Storage::disk('public')->exists($path)) {
            abort(404, 'File buku fisik tidak ditemukan di server.');
        }

        // 4. Return File sebagai Stream (Inline)
        $fullPath = Storage::disk('public')->path($path);

        return response()->file($fullPath, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $book->slug . '.pdf"'
        ]);
    }
}
