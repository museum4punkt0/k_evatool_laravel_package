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
        if (isset(request()->user()->id)) {
            $survey->created_by = request()->user()->id;
        }
    }

    /**
     * @return void
     */
    public function updating(EvaluationToolSurvey $survey)
    {
        $survey->slug = $this->createUniqueSlug($survey);
        if (isset(request()->user()->id)) {
            $survey->updated_by = request()->user()->id;
        }
    }

    /**
     * @return void
     */
    public function deleting(EvaluationToolSurvey $survey)
    {
        $survey->slug = $this->createUniqueSlug($survey);
        if (isset(request()->user()->id)) {
            $survey->deleted_by = request()->user()->id;
        }
    }

    private function createUniqueSlug($survey): string
    {
        $slugify = new Slugify();

        if (!$survey->slug) {
            $slug = $slugify->slugify($survey->name, "_");
        } else {
            $slug = $survey->slug;
        }

        if (EvaluationToolSurvey::withTrashed()->where("slug", $slug)->where("id", "!=", $survey->id)->first()) {
            return $slug . "_" . strtolower(Str::random(6));
        }

        return $slug;
    }
}
