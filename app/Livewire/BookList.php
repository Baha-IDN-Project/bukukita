<?php

namespace App\Livewire;

use App\Models\Book;
use Livewire\Component;
use Livewire\WithPagination;

class BookList extends Component
{
    use WithPagination;

    public string $search = '';

    public function render()
    {
        $books = Book::with('category') // Eager load relasi kategori
            ->where('judul', 'like', '%' . $this->search . '%')
            ->paginate(10);

        return view('livewire.book-list', [
            'books' => $books
        ]);
    }
}
