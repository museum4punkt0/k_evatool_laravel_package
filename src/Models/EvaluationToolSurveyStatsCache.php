<?php

namespace Twoavy\EvaluationTool\Models;

use Illuminate\Database\Eloquent\Model;

class EvaluationToolSurveyStatsCache extends Model
{
    protected $fillable = [
        "date",
        "survey_id"
    ];

    protected $dates = [
        "date"
    ];

    protected $casts = [
        "results" => "array",
    ];
}
