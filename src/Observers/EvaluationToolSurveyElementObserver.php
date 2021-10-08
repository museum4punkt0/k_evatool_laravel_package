<?php

namespace Twoavy\EvaluationTool\Observers;

use Twoavy\EvaluationTool\Models\EvaluationToolSurveyElement;
use Twoavy\EvaluationTool\Models\EvaluationToolSurveyElementType;

class EvaluationToolSurveyElementObserver
{
    /**
     * @param EvaluationToolSurveyElement $surveyElement
     * @return void
     */
    public function creating(EvaluationToolSurveyElement $surveyElement)
    {
        if (request()->has("survey_element_type")) {
            $surveyElement->survey_element_type_id = EvaluationToolSurveyElementType::where('key', request()->survey_element_type)
                ->first()
                ->id;
        }
        if (isset(request()->user()->id)) {
            $surveyElement->created_by = request()->user()->id;
            $surveyElement->updated_by = request()->user()->id;
        }
    }

    /**
     * @param EvaluationToolSurveyElement $surveyElement
     * @return void
     */
    public function created(EvaluationToolSurveyElement $surveyElement)
    {

    }

    /**
     * @return void
     */
    public function updating(EvaluationToolSurveyElement $surveyElement)
    {
        if (isset(request()->user()->id)) {
            $surveyElement->updated_by = request()->user()->id;
        }
    }

    /**
     * @return void
     */
    public function deleting(EvaluationToolSurveyElement $surveyElement)
    {
        if (isset(request()->user()->id)) {
            $surveyElement->deleted_by = request()->user()->id;
            $surveyElement->save();
        }
    }
}
