<?php

namespace Twoavy\EvaluationTool\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Twoavy\EvaluationTool\Models\EvaluationToolSurvey;

class EvaluationToolSurveySurveyStepController extends Controller
{
    /**
     * @param EvaluationToolSurvey $survey
     * @return JsonResponse
     */
    public function index(EvaluationToolSurvey $survey): JsonResponse
    {
        $surveySteps = $survey->survey_steps;
        return response()->json($surveySteps);
    }
}
