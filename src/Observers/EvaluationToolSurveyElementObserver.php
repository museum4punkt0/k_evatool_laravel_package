<?php

namespace Twoavy\EvaluationTool\Observers;

use Twoavy\EvaluationTool\Models\EvaluationToolSurveyElement;
use Twoavy\EvaluationTool\Models\EvaluationToolSurveyElementType;

class EvaluationToolSurveyElementObserver
{
    /**
     * @param EvaluationToolSurveyElement $element
     * @return void
     */
    public function creating(EvaluationToolSurveyElement $element)
    {
        if (request()->has("survey_element_type")) {
            $element->survey_element_type_id = EvaluationToolSurveyElementType::where('key', request()->survey_element_type)
                ->first()
                ->id;
        }
    }

    /**
     * @param EvaluationToolSurveyElement $element
     * @return void
     */
    public function created(EvaluationToolSurveyElement $element)
    {

    }

    /**
     * Handle the Section "updated" event.
     *
     * @return void
     */
    public function updating(EvaluationToolSurveyElement $element)
    {

    }
}
