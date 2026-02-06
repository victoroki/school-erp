<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CbcStrand extends Model
{
    protected $fillable = [
        'learning_area_id',
        'name',
        'description'
    ];

    public function learningArea()
    {
        return $this->belongsTo(CbcLearningArea::class, 'learning_area_id');
    }

    public function subStrands()
    {
        return $this->hasMany(CbcSubStrand::class, 'strand_id');
    }
}
