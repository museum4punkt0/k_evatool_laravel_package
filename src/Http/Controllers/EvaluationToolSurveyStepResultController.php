<?php

namespace Twoavy\EvaluationTool\Http\Controllers;


use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Twoavy\EvaluationTool\Models\EvaluationToolSurveyStepResult;

class EvaluationToolSurveyStepResultController extends Controller
{
    /**
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $surveyStepResults = EvaluationToolSurveyStepResult::all();
        return response()->json($surveyStepResults);
    }
}
