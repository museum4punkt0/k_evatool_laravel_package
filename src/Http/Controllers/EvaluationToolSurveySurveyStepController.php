<?php

namespace Twoavy\EvaluationTool\Http\Controllers;

namespace Twoavy\EvaluationTool\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Twoavy\EvaluationTool\Models\EvaluationToolSurvey;

class EvaluationToolSurveySurveyStepController extends Controller
{
    /**
     * @param EvaluationToolSurvey $evaluationToolSurvey
     * @return JsonResponse
     */
    public function index(EvaluationToolSurvey $survey): JsonResponse
    {
        return response()->json($survey);

		$surveys = $evaluationToolSurvey->survey_steps();
		return response()->json($surveys);
	}
}
