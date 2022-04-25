<?php

namespace Twoavy\EvaluationTool\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Twoavy\EvaluationTool\Transformers\EvaluationToolSurveyElementTransformer;

class EvaluationToolSurveyElement extends Model
{
    use HasFactory, SoftDeletes;

    // transforms data on api responses
    public $transformer = EvaluationToolSurveyElementTransformer::class;

    // number of items on paginated responses
    protected $perPage = 25;

    // fields that can be mass-assigned via create or fill methods
    protected $fillable = [
        "name",
        "description",
        "survey_element_type_id",
        "params",
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

    // specially cast fields
    protected $casts = [
        "params" => "object"
    ];

    protected $with = ["survey_element_type"];
    protected $withCount = [
        "survey_steps",
        "survey_results",
        "survey_demo_results"
    ];

    protected $appends = ["has_results"];

    public function survey_element_type(): HasOne
    {
        return $this->hasOne("Twoavy\EvaluationTool\Models\EvaluationToolSurveyElementType", "id", "survey_element_type_id");
    }

    public function survey_steps(): HasMany
    {
        return $this->hasMany("Twoavy\EvaluationTool\Models\EvaluationToolSurveyStep", "survey_element_id", "id");
    }

    public function surveys(): HasManyThrough
    {
        return $this->hasManyThrough("Twoavy\EvaluationTool\Models\EvaluationToolSurvey",
            "Twoavy\EvaluationTool\Models\EvaluationToolSurveyStep",
            "survey_element_id",
            "id",
            "id",
            "survey_id"
        )->distinct();
    }

    public function assets(): BelongsToMany
    {
        return $this->belongsToMany(
            EvaluationToolAsset::class,
            "evaluation_tool_asset_survey_element",
            "evaluation_tool_survey_element_id",
            "evaluation_tool_asset_id",
        );
    }

    /**
     * @return HasManyThrough
     */
    public function survey_results(): HasManyThrough
    {
        return $this->hasManyThrough(EvaluationToolSurveyStepResult::class,
            EvaluationToolSurveyStep::class,
            "survey_element_id",
            "survey_step_id",
            "id",
            "id")->where("demo", false);
    }

    /**
     * @return HasManyThrough
     */
    public function survey_demo_results(): HasManyThrough
    {
        return $this->hasManyThrough(EvaluationToolSurveyStepResult::class,
            EvaluationToolSurveyStep::class,
            "survey_element_id",
            "survey_step_id",
            "id",
            "id")->where("demo", true);
    }

    public function created_by_user(): HasOne
    {
        return $this->hasOne(User::class, "id", "created_by");
    }

        public function updated_by_user(): HasOne
    {
        return $this->hasOne(User::class, "id", "updated_by");
    }

    public function getHasResultsAttribute(): bool
    {
        return $this->survey_results()->count() > 0;
    }
}
