<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Peminjaman;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class EbookController extends Controller
{
    public function streamPDF(Book $book)
    {
        // 1. Validasi (Copy paste logika validasi kamu yg lama di sini)
        $hasAccess = Peminjaman::where('user_id', Auth::id())
            ->where('book_id', $book->id)
            ->where('status', 'dipinjam')
            // ->where(...) validasi tanggal dsb
            ->exists();

        if (!$hasAccess) {
            abort(403);
        }

        // 2. Cek File
        if (!Storage::disk('local')->exists($book->file_ebook)) {
            abort(404);
        }

        $path = Storage::disk('local')->path($book->file_ebook);

        // --- BAGIAN PENTING: PEMBERSIH DATA ---
        // Ini akan menghapus semua output (spasi/error php) yang buffer
        // sebelum mengirim file binary murni.
        if (ob_get_length()) {
            ob_end_clean();
        }

        // 3. Return dengan Header Lengkap
        return response()->file($path, [
            'Content-Type' => 'application/pdf',
            'Content-Length' => filesize($path), // Memberi tahu browser ukuran file total
            'Accept-Ranges' => 'bytes',          // Membolehkan PDF.js download per halaman (streaming)
        ]);
    }
}
