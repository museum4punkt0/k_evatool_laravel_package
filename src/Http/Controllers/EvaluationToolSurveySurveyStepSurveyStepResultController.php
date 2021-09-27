<?php

namespace Twoavy\EvaluationTool\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Twoavy\EvaluationTool\Http\Requests\EvaluationToolSurveyStepStoreRequest;
use Twoavy\EvaluationTool\Models\EvaluationToolSurvey;
use Twoavy\EvaluationTool\Models\EvaluationToolSurveyStep;
use Twoavy\EvaluationTool\Traits\EvaluationToolResponse;

class EvaluationToolSurveySurveyStepSurveyStepResultController extends Controller
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
        if ($surveyStep->survey_id !== $survey->id) {
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

    /**
     * @param EvaluationToolSurvey $survey
     * @param EvaluationToolSurveyStep $surveyStep
     * @param EvaluationToolSurveyStepStoreRequest $request
     * @return JsonResponse
     */
    public function setNextStep(EvaluationToolSurvey $survey, EvaluationToolSurveyStep $surveyStep, Request $request):
    JsonResponse
    {
        // check if survey id and step id match
        if ($surveyStep->survey_id !== $survey->id) {
            return $this->errorResponse("survey id does not match step id", 409);
        }

        $nextStep = EvaluationToolSurveyStep::find($request->nextStepId);
        if ($nextStep->survey_id !== $survey->id) {
            return $this->errorResponse("survey id does not match next step id", 409);
        }

        if ($surveyStep->id === $request->nextStepId) {
            return $this->errorResponse("step ids are equal. must be different", 409);
        }

        $surveyStep->next_step_id = $request->nextStepId;
        $surveyStep->save();
        return $this->showOne($surveyStep);
    }

    /**
     * @param EvaluationToolSurvey $survey
     * @param EvaluationToolSurveyStep $surveyStep
     * @return JsonResponse
     */
    public function removeNextStep(EvaluationToolSurvey $survey, EvaluationToolSurveyStep $surveyStep):
    JsonResponse
    {
        // check if survey id and step id match
        if ($surveyStep->survey_id !== $survey->id) {
            return $this->errorResponse("survey id does not match step id", 409);
        }

        $surveyStep->next_step_id = null;
        $surveyStep->save();
        return $this->showOne($surveyStep);
    }
}
