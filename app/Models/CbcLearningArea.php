<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CbcLearningArea extends Model
{
    protected $fillable = [
        'name',
        'code',
        'level',
        'description',
        'status'
    ];

    public function strands()
    {
        return $this->hasMany(CbcStrand::class, 'learning_area_id');
    }
}
