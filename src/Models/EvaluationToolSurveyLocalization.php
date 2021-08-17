<?php

namespace Twoavy\EvaluationTool\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Twoavy\EvaluationTool\Transformers\EvaluationToolSurveyLocalizationTransformer;

class EvaluationToolSurveyLocalization extends Model
{
    use HasFactory, SoftDeletes;
    // transforms data on api responses
    public $transformer = EvaluationToolSurveyLocalizationTransformer::class;

    // number of items on paginated responses
    protected $perPage = 25;

    // fields that can be mass-assigned via create or fill methods
    protected $fillable = [
        "model",
        "field",
        "value",
        "language_id",
    ];
}
