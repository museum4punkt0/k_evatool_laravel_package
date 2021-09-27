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
     * @param EvaluationToolSurveyStep $surveyStep
     * @return JsonResponse
     */
    public function index(EvaluationToolSurvey $survey, EvaluationToolSurveyStep $surveyStep): JsonResponse
    {
        $surveyStepResults = $surveyStep->survey_step_results;
        return $this->showAll($surveyStepResults);
    }

    /**
     * @param EvaluationToolSurvey $survey
     * @param EvaluationToolSurveyStep $surveyStep
     * @param EvaluationToolSurveyStepResult $surveyStepResult
     * @return JsonResponse
     */
    public function show(EvaluationToolSurvey $survey, EvaluationToolSurveyStep $surveyStep, EvaluationToolSurveyStepResult $surveyStepResult): JsonResponse
    {
        if ($surveyStep->survey_id !== $survey->id) {
            return $this->errorResponse("step does not belong to survey", 409);
        }

        return $this->showOne($surveyStepResult);
    }

    /**
     * @param EvaluationToolSurvey $survey
     * @param EvaluationToolSurveyStep $surveyStep
     * @param EvaluationToolSurveyStepResult $surveyStepResult
     * @return JsonResponse
     */
    public function destroy(EvaluationToolSurvey $survey, EvaluationToolSurveyStep $surveyStep, EvaluationToolSurveyStepResult $surveyStepResult):
    JsonResponse
    {
        if ($surveyStep->survey_id !== $survey->id) {
            return $this->errorResponse("step does not belong to survey", 409);
        }

        $surveyStepResult->delete();

        return $this->showOne($surveyStepResult);
    }


}
