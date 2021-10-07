<?php

namespace Twoavy\EvaluationTool\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
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
        'params'       => 'json',
        'result_value' => 'json',
        'demo'         => 'boolean'
    ];

    public function survey_step(): HasOne
    {
        return $this->hasOne("Twoavy\EvaluationTool\Models\EvaluationToolSurveyStep", "id", "survey_step_id");
    }

    public function language(): HasOne
    {
        return $this->hasOne("Twoavy\EvaluationTool\Models\EvaluationToolSurveyLanguage", "id","result_language_id");
    }

    public function created_by_user(): HasOne
    {
        return $this->hasOne(User::class, "id", "created_by");
    }

    public function updated_by_user(): HasOne
    {
        return $this->hasOne(User::class, "id", "updated_by");
    }
}
