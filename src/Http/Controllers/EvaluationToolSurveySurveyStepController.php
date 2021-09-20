<?php

namespace Twoavy\EvaluationTool\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Twoavy\EvaluationTool\Http\Requests\EvaluationToolSurveyStepStoreRequest;
use Twoavy\EvaluationTool\Models\EvaluationToolSurvey;
use Twoavy\EvaluationTool\Models\EvaluationToolSurveyStep;
use Twoavy\EvaluationTool\Traits\EvaluationToolResponse;

class EvaluationToolSurveySurveyStepController extends Controller
{
    use EvaluationToolResponse;

    public function __construct()
    {
        $this->middleware("auth:api")->except(["index", "show"]);
    }

    /**
     * @param EvaluationToolSurvey $survey
     * @return JsonResponse
     */
    public function index(EvaluationToolSurvey $survey): JsonResponse
    {
        $surveySteps = $survey->survey_steps;
        return $this->showAll($surveySteps);
    }

    /**
     * @param EvaluationToolSurvey $survey
     * @param EvaluationToolSurveyStep $surveyStep
     * @return JsonResponse
     */
    public function show(EvaluationToolSurvey $survey, EvaluationToolSurveyStep $surveyStep): JsonResponse
    {
        if($surveyStep->survey_id !== $survey->id) {
            return $this->errorResponse("step does not belong to survey", 409);
        }
        return $this->showOne($surveyStep);
    }

    /**
     * @param EvaluationToolSurvey $survey
     * @param EvaluationToolSurveyStepStoreRequest $request
     * @return JsonResponse
     */
    public function store(EvaluationToolSurvey $survey, EvaluationToolSurveyStepStoreRequest $request): JsonResponse
    {

        $surveyStep            = new EvaluationToolSurveyStep();
        $surveyStep->survey_id = $survey->id;
        $surveyStep->fill($request->all());
        $surveyStep->save();
        return $this->showOne($surveyStep);
    }

    /**
     * @param EvaluationToolSurvey $survey
     * @param EvaluationToolSurveyStep $surveyStep
     * @param EvaluationToolSurveyStepStoreRequest $request
     * @return JsonResponse
     */
    public function update(EvaluationToolSurvey $survey, EvaluationToolSurveyStep $surveyStep, EvaluationToolSurveyStepStoreRequest $request): JsonResponse
    {
        // check if survey id and step id match
        if ($surveyStep->survey_id !== $survey->id) {
            return $this->errorResponse("survey id does not match step id", 409);
        }
        $surveyStep->fill($request->all());
        $surveyStep->save();
        return $this->showOne($surveyStep);
    }
}
