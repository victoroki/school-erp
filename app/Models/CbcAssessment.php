<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CbcAssessment extends Model
{
    use HasFactory;

    public $table = 'cbc_assessments';

    protected $primaryKey = 'id';

    public $fillable = [
        'student_id',
        'learning_area_id',
        'strand_id',
        'sub_strand_id',
        'rating',          // 1=BE, 2=AE, 3=ME, 4=EE
        'remarks',
        'assessed_by',
        'assessment_date',
    ];

    protected $casts = [
        'rating' => 'integer',
        'assessment_date' => 'date',
    ];

    public const RATINGS = [
        1 => ['code' => 'BE', 'label' => 'Below Expectation'],
        2 => ['code' => 'AE', 'label' => 'Approaching Expectation'],
        3 => ['code' => 'ME', 'label' => 'Meeting Expectation'],
        4 => ['code' => 'EE', 'label' => 'Exceeding Expectation'],
    ];

    public function student(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function learningArea(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(CbcLearningArea::class, 'learning_area_id');
    }

    public function strand(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(CbcStrand::class, 'strand_id');
    }

    public function subStrand(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(CbcSubStrand::class, 'sub_strand_id');
    }
}
