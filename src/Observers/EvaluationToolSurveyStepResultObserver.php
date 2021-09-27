<?php

namespace Twoavy\EvaluationTool\Observers;

use Twoavy\EvaluationTool\Models\EvaluationToolSurveyStepResult;

class EvaluationToolSurveyStepResultObserver
{

    /**
     * @return void
     */
    public function creating(EvaluationToolSurveyStepResult $surveyStepResult)
    {
        if (request()->hasHeader('X-Demo')) {
            $surveyStepResult->demo = true;
        }
    }

    /**
     * @return void
     */
    public function updating(EvaluationToolSurveyStepResult $surveyStepResult)
    {
        if (request()->hasHeader('X-Demo')) {
            $surveyStepResult->demo = true;
        }
    }

    /**
     * @return void
     */
    public function deleted(EvaluationToolSurveyStepResult $surveyStepResult)
    {
        // Todo: Remove relevant result assets
    }
}
