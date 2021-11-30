<?php

namespace Twoavy\EvaluationTool\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Twoavy\EvaluationTool\Http\Requests\EvaluationToolSurveyStepResultAssetStoreRequest;
use Twoavy\EvaluationTool\Models\EvaluationToolSurvey;
use Twoavy\EvaluationTool\Models\EvaluationToolSurveyStep;
use Twoavy\EvaluationTool\Models\EvaluationToolSurveyStepResult;
use Twoavy\EvaluationTool\Models\EvaluationToolSurveyStepResultAsset;
use Twoavy\EvaluationTool\Traits\EvaluationToolResponse;

class EvaluationToolSurveyResultAssetController extends Controller
{
    use EvaluationToolResponse;

    public function __construct()
    {
        $this->middleware("auth:api");
        $this->audioDisk = Storage::disk("evaluation_tool_audio");
    }

    /**
     *  Retrieve a list of all survey step result assets
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $resultAssets = EvaluationToolSurveyStepResultAsset::all();
        return $this->showAll($resultAssets);
    }

    /**
     *  Retrieve a single survey step result asset
     *
     * @param EvaluationToolSurveyStepResultAsset $resultAsset
     * @return JsonResponse
     */
    public function show(EvaluationToolSurveyStepResultAsset $resultAsset): JsonResponse
    {
        return $this->showOne($resultAsset);
    }

    /**
     * Deletes a survey step result asset record
     *
     * @param EvaluationToolSurveyStepResultAsset $resultAsset
     * @return JsonResponse
     */
    public function destroy(EvaluationToolSurveyStepResultAsset $resultAsset): JsonResponse
    {
        // TODO: condition
        // if($surveyLanguage->survey_steps()->count() > 0) {
        //     return $this->errorResponse("cannot be deleted, has survey steps", 409);
        // }

        $resultAsset->delete();
        return $this->showOne($resultAsset->refresh());
    }
}
