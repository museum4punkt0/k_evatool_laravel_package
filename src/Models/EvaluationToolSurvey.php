<?php

namespace Twoavy\EvaluationTool\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Validation\Rule;
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
        "admin_layout"
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
        "survey_results"
    ];

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
            "id");
    }
}
