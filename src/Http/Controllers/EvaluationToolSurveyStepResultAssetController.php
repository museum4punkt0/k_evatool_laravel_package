<?php

namespace Twoavy\EvaluationTool\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Twoavy\EvaluationTool\Http\Requests\EvaluationToolSurveyStepResultAssetStoreRequest;
use Twoavy\EvaluationTool\Models\EvaluationToolSurveyStepResultAsset;
use Twoavy\EvaluationTool\Traits\EvaluationToolResponse;

class EvaluationToolSurveyStepResultAssetController extends Controller
{
    use EvaluationToolResponse;
    /**
     *  Retrieve a list of all survey step result assets
     * 
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $surveyStepResultAssets = EvaluationToolSurveyStepResultAsset::all();
        return response()->json($surveyStepResultAssets);
    }

    /**
     *  Retrieve a single survey step result asset
     *
     * @param EvaluationToolSurveyStepResultAsset $surveyStepResultAsset
     * @return JsonResponse
     */
    public function show(EvaluationToolSurveyStepResultAsset $surveyStepResultAsset): JsonResponse
    {
        return $this->showOne($surveyStepResultAsset);
    }

    /**
     * Stores a survey step result asset record
     *
     * @param EvaluationToolSurveyStepStoreRequest $request
     * @return JsonResponse
     */
    public function store(EvaluationToolSurveyStepResultAssetStoreRequest $request): JsonResponse
    {
        $surveyStepResultAsset = new EvaluationToolSurveyStepResultAsset();
        $surveyStepResultAsset->fill($request->all());
        $surveyStepResultAsset->save();

        return $this->showOne($surveyStepResultAsset->refresh());
    }

    /**
     * Updates a survey step result asset record
     *
     * @param EvaluationToolSurveyStepResultAssetStoreRequest $request
     * @param EvaluationToolSurveyStepResultAsset $surveyStepResultAsset
     * @return JsonResponse
     */
    public function update(EvaluationToolSurveyStepResultAssetStoreRequest $request, EvaluationToolSurveyStepResultAsset $surveyStepResultAsset): JsonResponse
    {
        $surveyStepResultAsset->fill($request->all());
        $surveyStepResultAsset->save();

        return $this->showOne($surveyStepResultAsset->refresh());
    }

    /**
     * Deletes a survey step result asset record
     *
     * @param EvaluationToolSurveyStepResultAsset $surveyStepResultAsset
     * @return JsonResponse
     */
    public function destroy(EvaluationToolSurveyStepResultAsset $surveyStepResultAsset): JsonResponse
    {
        // TODO: condition
        // if($surveyLanguage->survey_steps()->count() > 0) {
        //     return $this->errorResponse("cannot be deleted, has survey steps", 409);
        // }

        $surveyStepResultAsset->delete();
        return $this->showOne($surveyStepResultAsset->refresh());
    }
}
