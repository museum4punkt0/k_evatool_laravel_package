<?php

namespace Twoavy\EvaluationTool\Observers;

use Cocur\Slugify\Slugify;
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
        $slugify = new Slugify();

        if (!$survey->slug) {
            $slug = $slugify->slugify($survey->name, "_");
        } else {
            $slug = $survey->slug;
        }

        if (EvaluationToolSurvey::where("slug", $slug)->where("id", "!=", $survey->id)->first()) {
            return $slug . "_" . strtolower(Str::random(6));
        }

        return $slug;
    }
}
