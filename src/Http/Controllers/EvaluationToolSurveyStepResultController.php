<?php

namespace Twoavy\EvaluationTool\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Twoavy\EvaluationTool\Http\Requests\EvaluationToolSurveyStepResultStoreRequest;
use Illuminate\Http\Request;
use Twoavy\EvaluationTool\Models\EvaluationToolSurveyStepResult;
use Twoavy\EvaluationTool\Traits\EvaluationToolResponse;

class EvaluationToolSurveyStepResultController extends Controller
{
    use EvaluationToolResponse;

    /**
     *  Retrieve a list of all survey step results
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $surveyStepResults = EvaluationToolSurveyStepResult::all();
        return $this->showAll($surveyStepResults);
    }

    /**
     *  Retrieve a single survey step result
     *
     * @param EvaluationToolSurveyStepResultAsset $surveyStepResult
     * @return JsonResponse
     */
    public function show(EvaluationToolSurveyStepResult $surveyStepResult): JsonResponse
    {
        return $this->showOne($surveyStepResult);
    }

    /**
     * Stores a survey step result record
     *
     * @param EvaluationToolSurveyStepStoreRequest $request
     * @return JsonResponse
     */
    public function store(EvaluationToolSurveyStepResultStoreRequest $request): JsonResponse
    {
        $surveyStepResult= new EvaluationToolSurveyStepResult();
        $surveyStepResult->fill($request->all());
        $surveyStepResult->save();

        return $this->showOne($surveyStepResult->refresh());
    }

    /**
     * Updates a survey step result record
     *
     * @param EvaluationToolSurveyStepResultStoreRequest $request
     * @param EvaluationToolSurveyStepResult $surveyStepResult
     * @return JsonResponse
     */
    public function update(EvaluationToolSurveyStepResultStoreRequest $request, EvaluationToolSurveyStepResult $surveyStepResult): JsonResponse
    {
        $surveyStepResult->fill($request->all());
        $surveyStepResult->save();

        return $this->showOne($surveyStepResult->refresh());
    }

    /**
     * Deletes a survey step result record
     *
     * @param EvaluationToolSurveyStepResult $surveyStepResultAsset
     * @return JsonResponse
     */
    public function destroy(EvaluationToolSurveyStepResult $surveyStepResult): JsonResponse
    {
        // TODO: condition
        // if($surveyLanguage->survey_steps()->count() > 0) {
        //     return $this->errorResponse("cannot be deleted, has survey steps", 409);
        // }

        $surveyStepResult->delete();
        return $this->showOne($surveyStepResult->refresh());
    }
}
