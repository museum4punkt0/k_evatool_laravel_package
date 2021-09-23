<?php

namespace Twoavy\EvaluationTool\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Twoavy\EvaluationTool\Http\Requests\EvaluationToolSurveyStepResultAssetStoreRequest;
use Twoavy\EvaluationTool\Models\EvaluationToolSurveyStepResult;
use Twoavy\EvaluationTool\Models\EvaluationToolSurveyStepResultAsset;
use Twoavy\EvaluationTool\Traits\EvaluationToolResponse;

class EvaluationToolSurveyStepResultAssetController extends Controller
{
    use EvaluationToolResponse;

    public function __construct()
    {
        $this->middleware("auth:api")->except(["index", "show", "store", "update", "destroy"]);
        $this->audioDisk = Storage::disk("evaluation_tool_audio");
    }

    /**
     *  Retrieve a list of all survey step result assets
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $surveyStepResultAssets = EvaluationToolSurveyStepResultAsset::all();
        return $this->showAll($surveyStepResultAssets);
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
     * @param EvaluationToolSurveyStepResultAssetStoreRequest $request
     * @return JsonResponse
     */
    public function store(EvaluationToolSurveyStepResultAssetStoreRequest $request)
    {
        // Todo: Create Request
        if (!$request->has("audio")) {
            return $this->errorResponse("no audio provided", 409);
        }

        if (!$request->has("surveyStepResultId")) {
            return $this->errorResponse("no survey step result id provided", 409);
        }

        /*if (!$surveyStepResult = EvaluationToolSurveyStepResult::find($request->surveyStepResultId)) {
            return $this->errorResponse("survey step result not found", 409);
        }*/

        $fileContent = $request->audio;
        $fileContent = str_replace('data:audio/*;base64,', '', $fileContent);
        $hash        = substr(md5($fileContent),0,10);
        $file        = $this->audioDisk->put("recording_" . date('ymdhis') . "_" . $hash . ".wav", base64_decode($fileContent));
        return null;
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
