<?php

namespace Twoavy\EvaluationTool\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EvaluationToolSurvey extends Model
{
    use HasFactory;

    protected $fillable = ["name", "description", "published", "publish_up", "publish_down"];

    /**
     * @return HasMany
     */
    public function survey_steps(): HasMany
    {
        return $this->hasMany("Twoavy\EvaluationTool\Models\EvaluationToolSurveyStep");
    }
}
