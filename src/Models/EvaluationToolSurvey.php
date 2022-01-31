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
use Twoavy\EvaluationTool\Helpers\EvaluationToolHelper;
use Twoavy\EvaluationTool\Transformers\EvaluationToolSurveyTransformer;

class EvaluationToolSurvey extends Model
{
    use HasFactory, SoftDeletes;


    // transforms data on api responses
    public $transformer = EvaluationToolSurveyTransformer::class;

    // number of items on paginated responses
    protected $perPage = 25;

    // fields that can be mass-assigned via create or fill methods
    protected $fillable = [
        "name",
        "slug",
        "description",
        "published",
        "publish_up",
        "publish_down",
        "admin_layout",
        "setting_id"
    ];

    // date fields
    protected $dates = [
        "publish_up",
        "publish_down"
    ];

    // specially cast fields
    protected $casts = [
        "admin_layout" => "object"
    ];

    // relations that are included with their element count
    protected $withCount = [
        "survey_steps",
        "survey_results",
        "survey_demo_results"
    ];

    protected $appends = ["has_results"];

    /**
     * @return HasMany
     */
    public function survey_steps(): HasMany
    {
        return $this->hasMany(EvaluationToolSurveyStep::class, "survey_id");
    }

    /**
     * @return HasManyThrough
     */
    public function survey_results(): HasManyThrough
    {
        return $this->hasManyThrough(EvaluationToolSurveyStepResult::class,
            EvaluationToolSurveyStep::class,
            "survey_id",
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
            "survey_id",
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

    public function languages(): BelongsToMany
    {
        return $this->belongsToMany(EvaluationToolSurveyLanguage::class, "evaluation_tool_surveys_survey_languages", "survey_id", "survey_language_id");
    }

    public function getHasResultsAttribute(): bool
    {
        return $this->survey_results()->count() > 0;
    }

    public function setting(): HasOne
    {
        return $this->hasOne(EvaluationToolSetting::class, "id", "setting_id");
    }
}
