<?php

namespace App\Http\Controllers;

namespace Twoavy\EvaluationTool\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Twoavy\EvaluationTool\Models\EvaluationToolSurveyLocalization;

class EvaluationToolSurveyLocalizationController extends Controller
{
    /**
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $surveyLocalizations = EvaluationToolSurveyLocalization::all();
        return response()->json($surveyLocalizations);
    }
}
