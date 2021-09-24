<?php

namespace Twoavy\EvaluationTool\Observers;

use Illuminate\Support\Str;
use Twoavy\EvaluationTool\Models\EvaluationToolSurvey;

class EvaluationToolSurveyObserver
{
    /**
     * @param EvaluationToolSurvey $survey
     * @return void
     */
    public function creating(EvaluationToolSurvey $survey)
    {
        $survey->slug = $this->createUniqueSlug($survey);
    }

    /**
     * @return void
     */
    public function updating(EvaluationToolSurvey $survey)
    {
        $survey->slug = $this->createUniqueSlug($survey);
    }

    private function createUniqueSlug($survey): string
    {
        if (!$survey->slug) {
            $slug = Str::slug($survey->name, "_");
        } else {
            $slug = $survey->slug;
        }

        if (EvaluationToolSurvey::where("slug", $slug)->where("id", "!=", $survey->id)->first()) {
            return $slug . "_" . strtolower(Str::random(6));
        }

        return $slug;
    }
}
