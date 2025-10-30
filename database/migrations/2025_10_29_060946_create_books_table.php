<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('books', function (Blueprint $table) {
        $table->id();

        $table->foreignId('category_id')->constrained('categories')->onDelete('cascade');

        $table->string('judul');
        $table->string('penulis')->nullable();
        $table->integer('lisensi')->default(1);
        $table->string('file_ebook');
        $table->string('gambar_cover')->nullable();
        $table->string('slug', 100)->unique();
        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('books');
    }
};
