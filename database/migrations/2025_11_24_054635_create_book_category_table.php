<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Buat Tabel Pivot (Penghubung)
        Schema::create('book_category', function (Blueprint $table) {
            $table->id();
            $table->foreignId('book_id')->constrained('books')->onDelete('cascade');
            $table->foreignId('category_id')->constrained('categories')->onDelete('cascade');
            // Mencegah duplikat relasi
            $table->unique(['book_id', 'category_id']);
        });

        // 2. Hapus kolom category_id lama di tabel books (jika ada)
        if (Schema::hasColumn('books', 'category_id')) {
            Schema::table('books', function (Blueprint $table) {
                // Drop foreign key (nama constraint mungkin berbeda, kita coba array syntax)
                $table->dropForeign(['category_id']);
                $table->dropColumn('category_id');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('book_category');

        Schema::table('books', function (Blueprint $table) {
            $table->foreignId('category_id')->nullable()->constrained('categories');
        });
    }
};
