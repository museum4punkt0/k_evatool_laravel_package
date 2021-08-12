<?php

namespace Twoavy\EvaluationTool\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Twoavy\EvaluationTool\Models\EvaluationToolSurveyStepResultAsset;

class EvaluationToolSurveyStepResultAssetController extends Controller
{
    /**
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $surveyStepResultAssets = EvaluationToolSurveyStepResultAsset::all();
        return response()->json($surveyStepResultAssets);
    }
}
