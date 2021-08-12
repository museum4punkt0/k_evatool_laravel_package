<?php

namespace Twoavy\EvaluationTool\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Twoavy\EvaluationTool\Models\EvaluationToolSurveyStep;

class EvaluationToolSurveyStepController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(): JsonResponse
    {
        $surveySteps = EvaluationToolSurveyStep::all();
        return response()->json($surveySteps);
    }
}
