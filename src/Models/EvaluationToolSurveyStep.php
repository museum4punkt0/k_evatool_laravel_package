<?php

namespace Twoavy\EvaluationTool\Models;

use App\Models\User;
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
        "result_based_next_steps",
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
        "time_based_steps"        => "object",
        "result_based_next_steps" => "object",
        "demo"                    => "boolean"
    ];

    protected $with = ["survey_element"];

    protected $withCount = [
        "survey_step_results",
        "survey_step_demo_results",
    ];

    public function survey(): HasOne
    {
        return $this->hasOne("Twoavy\EvaluationTool\Models\EvaluationToolSurvey", "id", "survey_id");
    }

    public function survey_element(): HasOne
    {
        return $this->hasOne("Twoavy\EvaluationTool\Models\EvaluationToolSurveyElement", "id", "survey_element_id");
    }

    public function survey_step_results(): HasMany
    {
        return $this->hasMany("Twoavy\EvaluationTool\Models\EvaluationToolSurveyStepResult", "survey_step_id", "id")
            ->where(["demo" => false]);
    }

    public function survey_step_demo_results(): HasMany
    {
        return $this->hasMany("Twoavy\EvaluationTool\Models\EvaluationToolSurveyStepResult", "survey_step_id", "id")
            ->where(["demo" => true]);
    }

    public function survey_step_result_by_uuid(): HasOne
    {
        return $this->hasOne("Twoavy\EvaluationTool\Models\EvaluationToolSurveyStepResult", "survey_step_id", "id")->where(['session_id' => request()->uuid]);
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
