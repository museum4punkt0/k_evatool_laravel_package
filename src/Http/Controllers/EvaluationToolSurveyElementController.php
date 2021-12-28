<?php

namespace Twoavy\EvaluationTool\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Twoavy\EvaluationTool\Http\Requests\EvaluationToolSurveyElementStoreRequest;
use Twoavy\EvaluationTool\Models\EvaluationToolAsset;
use Twoavy\EvaluationTool\Models\EvaluationToolSurveyElement;
use Twoavy\EvaluationTool\Traits\EvaluationToolResponse;

class EvaluationToolSurveyElementController extends Controller
{
    use EvaluationToolResponse;

    public function __construct()
    {
        $this->middleware("auth:api");
    }

    /**
     * Retrieve a list of all survey elements
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $surveyElements = EvaluationToolSurveyElement::all();
        return $this->showAll($surveyElements);
    }

    /**
     *  Retrieve a single survey element
     *
     * @param EvaluationToolSurveyElement $surveyElement
     * @return JsonResponse
     */
    public function show(EvaluationToolSurveyElement $surveyElement): JsonResponse
    {
        return $this->showOne($surveyElement);
    }

    /**
     * Stores a survey element record
     *
     * @param EvaluationToolSurveyElementStoreRequest $request
     * @return JsonResponse
     */
    public function store(EvaluationToolSurveyElementStoreRequest $request): JsonResponse
    {
        $surveyElement = new EvaluationToolSurveyElement();
        $surveyElement->fill($request->all());
        $surveyElement->save();

        return $this->showOne($surveyElement->refresh());
    }

    /**
     * Updates a survey element record
     *
     * @param EvaluationToolSurveyElementStoreRequest $request
     * @param EvaluationToolSurveyElement $surveyElement
     * @return JsonResponse
     */
    public function update(EvaluationToolSurveyElementStoreRequest $request, EvaluationToolSurveyElement $surveyElement): JsonResponse
    {
        $surveyElement->fill($request->all());
        $surveyElement->save();

        return $this->showOne($surveyElement->refresh());
    }

    /**
     * Deletes a survey element record
     *
     * @param EvaluationToolSurveyElement $surveyElement
     * @return JsonResponse
     */
    public function destroy(EvaluationToolSurveyElement $surveyElement): JsonResponse
    {
        // TODO: check condition
        // if($surveyElement->survey_steps()->count() > 0) {
        //     return $this->errorResponse("cannot be deleted, has survey steps", 409);
        // }

        $surveyElement->delete();
        return $this->showOne($surveyElement->refresh());
    }

    public static function readSurveyElementAssets()
    {
        DB::table("evaluation_tool_asset_survey_element")->truncate();
        EvaluationToolSurveyElement::all()->each(function ($surveyElement) {
            self::assignAssets($surveyElement);
        });
    }

    public static function assignAssets($surveyElement)
    {

        // yay nay
        if ($surveyElement->survey_element_type_id == 5) {
            $surveyElement->assets()->detach();
            if (isset($surveyElement->params->assetIds) && is_array($surveyElement->params->assetIds) && !empty($surveyElement->params->assetIds)) {
                $surveyElement->assets()->attach($surveyElement->params->assetIds);
            }
        }

        // video
        if ($surveyElement->survey_element_type_id == 7) {
            $surveyElement->assets()->detach();
            if (isset($surveyElement->params->videoAssetId) && EvaluationToolAsset::find($surveyElement->params->videoAssetId)) {
                $surveyElement->assets()->attach($surveyElement->params->videoAssetId);
            }
        }
    }
}
