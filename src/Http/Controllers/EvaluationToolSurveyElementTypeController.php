<?php

namespace Twoavy\EvaluationTool\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Twoavy\EvaluationTool\Models\EvaluationToolSurveyStepType;

class EvaluationToolSurveyElementTypeController extends Controller
{

    /**
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $surveyElementTypes = EvaluationToolSurveyStepType::all();
        return response()->json($surveyElementTypes);
    }
}
