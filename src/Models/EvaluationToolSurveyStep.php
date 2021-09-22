<?php

namespace Twoavy\EvaluationTool\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;
use Twoavy\EvaluationTool\Transformers\EvaluationToolSurveyStepTransformer;

class EvaluationToolSurveyStep extends Model
{
    use HasFactory, SoftDeletes;

    // transforms data on api responses
    public $transformer = EvaluationToolSurveyStepTransformer::class;

    // number of items on paginated responses
    protected $perPage = 25;

    // fields that can be mass-assigned via create or fill methods
    protected $fillable = [
        "name",
        "survey_id",
        "survey_element_id",
        "group",
        "next_step_id",
        "time_based_steps",
        "allow_skip",
        "published",
        "publish_up",
        "publish_down",
        "admin_layout"
    ];

    // date fields
    protected $dates = [
        "publish_up",
        "publish_down"
    ];

    protected $casts = [
        "time_based_steps" => "object"
    ];

    protected $with = ["survey_element"];

    public function survey_element(): HasOne
    {
        return $this->hasOne("Twoavy\EvaluationTool\Models\EvaluationToolSurveyElement", "id", "survey_element_id");
    }

    public function survey_results(): HasMany
    {
        return $this->hasMany("Twoavy\EvaluationTool\Models\EvaluationToolSurveyStepResult", "survey_step_id", "id");
    }

    public function survey_results_by_uuid(): HasMany
    {
        return $this->hasMany("Twoavy\EvaluationTool\Models\EvaluationToolSurveyStepResult", "survey_step_id", "id")->where('session_id', request()->uuid);
    }
}
