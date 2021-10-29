<?php

namespace Twoavy\EvaluationTool\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;
use Twoavy\EvaluationTool\Helpers\EvaluationToolHelper;
use Twoavy\EvaluationTool\Transformers\EvaluationToolSurveyStepResultCombinedTransformer;
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
        "params",
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
        "params"                  => "object",
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

    public function survey_element_type(): HasOneThrough
    {
        return $this->hasOneThrough(
            EvaluationToolSurveyElementType::class,
            EvaluationToolSurveyElement::class,
            "id",
            "id",
            "survey_element_id",
            "survey_element_type_id"
        );
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

    public function survey_step_result_by_uuid(): HasMany
    {
        return $this->hasMany("Twoavy\EvaluationTool\Models\EvaluationToolSurveyStepResult", "survey_step_id", "id")->where(['session_id' => request()
            ->uuid]);
    }

    public function previous_steps(): HasMany
    {
        return $this->hasMany("Twoavy\EvaluationTool\Models\EvaluationToolSurveyStep", "next_step_id", "id");
    }

    public function created_by_user(): HasOne
    {
        return $this->hasOne(User::class, "id", "created_by");
    }

    public function updated_by_user(): HasOne
    {
        return $this->hasOne(User::class, "id", "updated_by");
    }

    public function getTimebasedStepsResolvedAttribute()
    {
        if (isset($this->time_based_steps) && is_array($this->time_based_steps)) {
            $steps = collect($this->time_based_steps)->map(function ($step) {
                $step->step = EvaluationToolHelper::transformModel(EvaluationToolSurveyStep::find($step->stepId), true, EvaluationToolSurveyStepResultCombinedTransformer::class);
                return $step;
            });
            return $steps;
        }

        return $this->time_based_steps;
    }
}
