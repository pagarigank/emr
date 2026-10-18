<?php

namespace App\Models\MICU;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MicuAssessmentValue extends Model
{
    protected $table = 'micu_assessment_values';
    public $timestamps = false;
    public $incrementing = true;
    protected $guarded = ['id'];

    public function assessment(): BelongsTo
    {
        return $this->belongsTo(MicuAssessment::class, 'micu_assessment_id');
    }

    public function parameter(): BelongsTo
    {
        return $this->belongsTo(MicuAssessmentParameter::class, 'micu_assessment_parameter_id');
    }
}
