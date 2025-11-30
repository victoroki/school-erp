<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    public $table = 'books';
    protected $primaryKey = 'book_id';

    public $fillable = [
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

    protected $casts = [
        'title' => 'string',
        'author' => 'string',
        'isbn' => 'string',
        'publisher' => 'string',
        'edition' => 'string',
        'price' => 'decimal:2',
        'shelf_location' => 'string',
        'added_date' => 'date',
        'description' => 'string'
    ];

    public static array $rules = [
        'title' => 'required|string|max:255',
        'author' => 'required|string|max:255',
        'category_id' => 'nullable',
        'isbn' => 'nullable|string|max:20',
        'publisher' => 'nullable|string|max:100',
        'publication_year' => 'nullable',
        'edition' => 'nullable|string|max:50',
        'price' => 'nullable|numeric',
        'pages' => 'nullable',
        'quantity' => 'required',
        'available_quantity' => 'required',
        'shelf_location' => 'nullable|string|max:50',
        'added_date' => 'required',
        'description' => 'nullable|string|max:65535',
        'created_at' => 'nullable',
        'updated_at' => 'nullable'
    ];

    public function category(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\BookCategory::class, 'category_id');
    }

    public function bookIssues(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\BookIssue::class, 'book_id');
    }
}
