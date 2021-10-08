<?php

namespace Twoavy\EvaluationTool\Observers;

use Twoavy\EvaluationTool\Models\EvaluationToolSurveyLanguage;

class EvaluationToolSurveyLanguageObserver
{

    /**
     * @param EvaluationToolSurveyLanguage $surveyLanguage
     */
    public function creating(EvaluationToolSurveyLanguage $surveyLanguage)
    {
        if (isset(request()->user()->id)) {
            $surveyLanguage->created_by = request()->user()->id;
            $surveyLanguage->updated_by = request()->user()->id;
        }
    }

    public function updating(EvaluationToolSurveyLanguage $surveyLanguage)
    {
        if (isset(request()->user()->id)) {
            $surveyLanguage->updated_by = request()->user()->id;
        }
    }

    /**
     * @param EvaluationToolSurveyLanguage $surveyLanguage
     */
    public function deleting(EvaluationToolSurveyLanguage $surveyLanguage)
    {
        if (isset(request()->user()->id)) {
            $surveyLanguage->deleted_by = request()->user()->id;
            $surveyLanguage->save();
        }
    }
}
