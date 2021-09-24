<?php

namespace Twoavy\EvaluationTool\Observers;

use Twoavy\EvaluationTool\Models\EvaluationToolSurveyStep;

class EvaluationToolSurveyStepObserver
{

    /**
     * @param EvaluationToolSurveyStep $surveyStep
     * @return void
     */
    public function created(EvaluationToolSurveyStep $surveyStep)
    {
        if ($surveyStep->survey_element->survey_element_type === "video") {
            $this->setParentStepIds($surveyStep);
        }
    }

    /**
     * @return void
     */
    public function updated(EvaluationToolSurveyStep $surveyStep)
    {
        if ($surveyStep->survey_element->survey_element_type === "video") {
            $this->setParentStepIds($surveyStep);
        }
    }

    private function setParentStepIds($surveyStep)
    {
        if(isset($surveyStep->params->timeBasedSteps) && is_array($surveyStep->params->timeBasedSteps)) {
            foreach($surveyStep->params->timeBasedSteps as $timeBasedStep) {
                if($step = EvaluationToolSurveyStep::find($timeBasedStep->stepId)) {
                    $step->parent_step_id = $surveyStep->id;
                }
            }
        }
    }
}
