<?php

namespace Twoavy\EvaluationTool\Observers;

use Twoavy\EvaluationTool\Http\Controllers\EvaluationToolSurveySurveyRunController;
use Twoavy\EvaluationTool\Models\EvaluationToolSurveyStep;
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

    public function created(EvaluationToolSurveyStepResult $surveyStepResult)
    {
        // get position in survey and set suvrey finished if applicable
        if (app()->runningInConsole()) {
            $runController = new EvaluationToolSurveySurveyRunController();
            $surveyStep    = EvaluationToolSurveyStep::find($surveyStepResult->survey_step_id);
            $survey        = $surveyStep->survey;
            $surveySteps   = $survey->survey_steps;

            foreach ($surveySteps as $surveyStep) {
                $resultsByUuid            = $runController->getResultsByUuid($surveyStep, $surveyStepResult->session_id);
                $surveyStep->resultByUuid = $resultsByUuid->result;
                $surveyStep->isAnswered   = $resultsByUuid->isAnswered;
            }
            $surveyPosition = $runController->getPositionWithinSurvey($survey->survey_steps);

            if ($surveyPosition["currentStep"] == -1) {
                $surveyStepResult->survey_finished = true;
                $surveyStepResult->save();
            }
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
