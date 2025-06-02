<?php

namespace App\Repositories;

use App\Models\Book;
use App\Repositories\BaseRepository;

class BookRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'title',
        'author',
        'category_id',
        'isbn',
        'publisher',
        'publication_year',
        'edition',
        'price',
        'pages',
        'quantity',
        'available_quantity',
        'shelf_location',
        'added_date',
        'description'
    ];

    public function getFieldsSearchable(): array
    {
        return $this->fieldSearchable;
    }

    public function model(): string
    {
        return Book::class;
    }
}
