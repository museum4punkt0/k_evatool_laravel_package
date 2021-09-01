<?php

namespace Twoavy\EvaluationTool\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
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
        "params" => "json"
    ];

    protected $with = ["survey_element_type"];

    public function survey_element_type(): HasOne
    {
        return $this->hasOne("Twoavy\EvaluationTool\Models\EvaluationToolSurveyElementType", "id","survey_element_type_id");
    }
}
