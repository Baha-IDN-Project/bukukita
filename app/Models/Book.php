<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Category;

class Book extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    // UBAH: Relasi menjadi BelongsToMany
    public function categories()
    {
        return $this->belongsToMany(Category::class, 'book_category');
    }

    public function peminjaman()
    {
        return $this->hasMany(Peminjaman::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }
}
