<?php

namespace Twoavy\EvaluationTool\Observers;

use Twoavy\EvaluationTool\Models\EvaluationToolSurvey;
use Twoavy\EvaluationTool\Models\EvaluationToolSurveyStep;

class EvaluationToolSurveyStepObserver
{

    /**
     * @param EvaluationToolSurveyStep $surveyStep
     */
    public function creating(EvaluationToolSurveyStep $surveyStep)
    {
        if (isset(request()->user()->id)) {
            $surveyStep->created_by = request()->user()->id;
            $surveyStep->updated_by = request()->user()->id;
        }

        // make first step if no steps exist within survey
        if (EvaluationToolSurveyStep::where("survey_id", $surveyStep->survey_id)->get()->count() == 0) {
            $surveyStep->is_first_step = true;
        }
    }

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

    public function updating(EvaluationToolSurveyStep $surveyStep)
    {
        if ($surveyStep->survey_element->survey_element_type->key === "video") {
            $timeBasedSteps = $surveyStep->time_based_steps;
            if (is_array($timeBasedSteps)) {
                usort($timeBasedSteps, function ($a, $b) {
                    if ($a->timecode == $b->timecode) {
                        return 0;
                    }
                    return ($a->timecode < $b->timecode) ? -1 : 1;
                });
                $surveyStep->time_based_steps = $timeBasedSteps;
            }
        }

        if (isset(request()->user()->id)) {
            $surveyStep->updated_by = request()->user()->id;
        }
    }

    /**
     * @return void
     */
    public function updated(EvaluationToolSurveyStep $surveyStep)
    {
        if ($surveyStep->survey_element_type->key === "video") {
            $this->setParentStepIds($surveyStep);
        }
    }

    /**
     * @param EvaluationToolSurveyStep $surveyStep
     */
    public function deleting(EvaluationToolSurveyStep $surveyStep)
    {
        if (isset(request()->user()->id)) {
            $surveyStep->deleted_by = request()->user()->id;
            $surveyStep->save();
        }
    }

    /**
     * @return void
     */
    public function deleted(EvaluationToolSurveyStep $surveyStep)
    {
        // check if first step
        $isFirstStep = false;
        if ($surveyStep->is_first_step) {
            $isFirstStep = true;
        }

        $surveyStep->is_first_step = null;
        $surveyStep->save();

        // reassign first step
        if ($isFirstStep) {
            if ($firstStep = EvaluationToolSurveyStep::where("survey_id", $surveyStep->survey_id)->first()) {
                $firstStep->is_first_step = true;
                $firstStep->save();
            }
        }

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

    private function setParentStepIds($surveyStep)
    {
        EvaluationToolSurveyStep::where("parent_step_id", $surveyStep->id)->update(["parent_step_id" => null]);

        if (isset($surveyStep->time_based_steps) && is_array($surveyStep->time_based_steps)) {
            EvaluationToolSurveyStep::where("parent_step_id", $surveyStep->id)->get()->each(function ($step) {
                $step->parent_step_id = null;
                $step->save();
            });
            foreach ($surveyStep->time_based_steps as $timeBasedStep) {
                if ($step = EvaluationToolSurveyStep::find($timeBasedStep->stepId)) {
                    $step->parent_step_id = $surveyStep->id;
                    $step->save();
                }
            }
        }
    }
}
