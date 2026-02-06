<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CbcSubStrand extends Model
{
    protected $fillable = [
        'strand_id',
        'name',
        'description'
    ];

    public function strand()
    {
        return $this->belongsTo(CbcStrand::class, 'strand_id');
    }
}
