<?php

namespace Twoavy\EvaluationTool\Observers;

use Twoavy\EvaluationTool\Models\EvaluationToolSurvey;
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
    public function deleted(EvaluationToolSurveyStep $surveyStep)
    {
        $survey = EvaluationToolSurvey::find($surveyStep->survey_id);
        if (isset($survey->admin_layout) && is_array($survey->admin_layout)) {
            $adminLayout = $survey->admin_layout;
            foreach ($adminLayout as $s => $step) {
                if ($step->id == $surveyStep->id) {
                    array_splice($adminLayout, $s, 1);
                }
            }
        }
        $surveyStep->admin_layout = $adminLayout;
        $survey->save();

        if ($previousStep = EvaluationToolSurveyStep::where("next_step_id", $surveyStep->id)->first()) {
            $previousStep->next_step_id = null;
            $previousStep->save();
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
