<?php

namespace Twoavy\EvaluationTool\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
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
        "survey_steps"
    ];

    /**
     * @return HasMany
     */
    public function survey_steps(): HasMany
    {
        return $this->hasMany(EvaluationToolSurveyStep::class, "survey_id");
    }

    public static function rules(): array
    {
        return [
            "name"                   => "required|min:2|max:100",
            "survey_element_type_id" => [
                "required",
                Rule::exists("evaluation_tool_survey_element_types", "id")
            ]
        ];
    }
}
