<?php

namespace Twoavy\EvaluationTool\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Twoavy\EvaluationTool\Http\Requests\EvaluationToolSurveyStepStoreRequest;
use Twoavy\EvaluationTool\Models\EvaluationToolSurvey;
use Twoavy\EvaluationTool\Models\EvaluationToolSurveyStep;
use Twoavy\EvaluationTool\Models\EvaluationToolSurveyStepResult;
use Twoavy\EvaluationTool\Traits\EvaluationToolResponse;

class EvaluationToolSurveySurveyStepSurveyStepResultController extends Controller
{
    use EvaluationToolResponse;

    public function __construct()
    {
        $this->middleware("auth:api");
    }

    /**
     * @param EvaluationToolSurvey $survey
     * @param EvaluationToolSurveyStep $step
     * @return JsonResponse
     */
    public function index(EvaluationToolSurvey $survey, EvaluationToolSurveyStep $step): JsonResponse
    {
        if ($step->survey_id !== $survey->id) {
            return $this->errorResponse("step does not belong to survey", 409);
        }

        $stepResults = $step->survey_step_results;
        return $this->showAll($stepResults);
    }

    /**
     * @param EvaluationToolSurvey $survey
     * @param EvaluationToolSurveyStep $step
     * @param EvaluationToolSurveyStepResult $stepResult
     * @return JsonResponse
     */
    public function show(EvaluationToolSurvey $survey, EvaluationToolSurveyStep $step, EvaluationToolSurveyStepResult $stepResult): JsonResponse
    {
        if ($step->survey_id !== $survey->id) {
            return $this->errorResponse("step does not belong to survey", 409);
        }

        if ($stepResult->survey_step_id !== $step->id) {
            return $this->errorResponse("result does not belong to step", 409);
        }

        return $this->showOne($stepResult);
    }

    /**
     * @param EvaluationToolSurvey $survey
     * @param EvaluationToolSurveyStep $step
     * @param EvaluationToolSurveyStepResult $stepResult
     * @return JsonResponse
     */
    public function destroy(EvaluationToolSurvey $survey, EvaluationToolSurveyStep $step, EvaluationToolSurveyStepResult $stepResult):
    JsonResponse
    {
        if ($step->survey_id !== $survey->id) {
            return $this->errorResponse("step does not belong to survey", 409);
        }

        if ($stepResult->survey_step_id !== $step->id) {
            return $this->errorResponse("result does not belong to step", 409);
        }

        $stepResult->delete();

        return $this->showOne($stepResult);
    }


}
