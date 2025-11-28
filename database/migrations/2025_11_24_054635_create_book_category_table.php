<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('book_category', function (Blueprint $table) {
            $table->id();
            $table->foreignId('book_id')->constrained('books')->onDelete('cascade');
            $table->foreignId('category_id')->constrained('categories')->onDelete('cascade');
            // Mencegah duplikat relasi
            $table->unique(['book_id', 'category_id']);
        });

        if (Schema::hasColumn('books', 'category_id')) {
            Schema::table('books', function (Blueprint $table) {
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
