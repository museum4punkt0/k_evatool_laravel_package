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
        if ($surveyStep->survey_element->survey_element_type->key === "video") {
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
        EvaluationToolSurveyStep::where("parent_step_id", $surveyStep->id)->update(["parent_step_id" => null]);

        if (isset($surveyStep->time_based_steps) && is_array($surveyStep->time_based_steps)) {
            foreach ($surveyStep->time_based_steps as $timeBasedStep) {
                if ($step = EvaluationToolSurveyStep::find($timeBasedStep->stepId)) {
                    $step->parent_step_id = $surveyStep->id;
                    $step->save();
                }
            }
        }
    }
}
