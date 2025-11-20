<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('books', function (Blueprint $table) {
            // Tambahkan kolom baru
            $table->integer('jumlah_dipinjam')->default(0)->after('lisensi');
        });
    }

    public function down(): void
    {
        Schema::table('books', function (Blueprint $table) {
            // Hapus kolom saat rollback
            $table->dropColumn('jumlah_dipinjam');
        });
    }
};

