<?php

namespace Twoavy\EvaluationTool\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Twoavy\EvaluationTool\Transformers\EvaluationToolSurveyStepResultTransformer;

class EvaluationToolSurveyStepResult extends Model
{
    use HasFactory, SoftDeletes;

    // transforms data on api responses
    public $transformer = EvaluationToolSurveyStepResultTransformer::class;

    // number of items on paginated responses
    protected $perPage = 25;

    // fields that can be mass-assigned via create or fill methods
    protected $fillable = [
        "survey_step_id",
        "presented_at",
        "answered_at",
        "changed_answer",
        "session_id",
        "result_language_id",
        "result_value",
        "is_skipped",
        "time",
        "params",
    ];
    protected $casts = [
        'params' => 'json',
        'result_value' => 'json',
    ];
}
