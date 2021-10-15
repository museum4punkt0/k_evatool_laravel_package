<?php

namespace Twoavy\EvaluationTool\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
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
    protected $withCount = ["survey_steps"];

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

    public function created_by_user(): HasOne
    {
        return $this->hasOne(User::class, "id", "created_by");
    }

    public function updated_by_user(): HasOne
    {
        return $this->hasOne(User::class, "id", "updated_by");
    }

    public function getAssetsAttribute()
    {
        if ($this->survey_element_type->key == "video" && isset($this->params->videoAssetId)) {
            if ($videoAsset = EvaluationToolAsset::find($this->params->videoAssetId)) {
                return $videoAsset->only("id", "urls");
            }
            return null;
        }
        return null;
    }
}
