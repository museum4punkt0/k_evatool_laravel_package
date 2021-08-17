<?php

namespace Twoavy\EvaluationTool\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Twoavy\EvaluationTool\Http\Requests\EvaluationToolSurveyStepStoreRequest;
use Twoavy\EvaluationTool\Models\EvaluationToolSurveyStep;
use Twoavy\EvaluationTool\Traits\EvaluationToolResponse;

class EvaluationToolSurveyStepController extends Controller
{
    /**
     * Retrieve a list of all survey steps
     * 
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $surveySteps = EvaluationToolSurveyStep::all();
        return response()->json($surveySteps);
    }

    /**
     *  Retrieve a single survey step
     *
     * @param EvaluationToolSurveyStep $surveyStep
     * @return JsonResponse
     */
    public function show(EvaluationToolSurveyStep $surveyStep): JsonResponse
    {
        return $this->showOne($surveyStep);
    }

    /**
     * Stores a survey step record
     *
     * @param EvaluationToolSurveyStepStoreRequest $request
     * @return JsonResponse
     */
    public function store(EvaluationToolSurveyStepStoreRequest $request): JsonResponse
    {
        $surveyStep = new EvaluationToolSurveyStep();
        $surveyStep->fill($request->all());
        $surveyStep->save();

        return $this->showOne($surveyStep->refresh());
    }

    /**
     * Updates a survey step record
     *
     * @param EvaluationToolSurveyStepStoreRequest $request
     * @param EvaluationToolSurveyStep $surveyStep
     * @return JsonResponse
     */
    public function update(EvaluationToolSurveyStepStoreRequest $request, EvaluationToolSurveyStep $surveyStep): JsonResponse
    {
        $surveyStep->fill($request->all());
        $surveyStep->save();

        return $this->showOne($surveyStep->refresh());
    }

    /**
     * Deletes a survey step record
     *
     * @param EvaluationToolSurveyStep $surveyStep
     * @return JsonResponse
     */
    public function destroy(EvaluationToolSurveyStep $surveyStep): JsonResponse
    {
        // TODO: condition
        // if($surveyLanguage->survey_steps()->count() > 0) {
        //     return $this->errorResponse("cannot be deleted, has survey steps", 409);
        // }

        $surveyStep->delete();
        return $this->showOne($surveyStep->refresh());
    }
}
