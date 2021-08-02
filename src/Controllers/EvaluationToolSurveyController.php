<?php

namespace Twoavy\EvaluationTool\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Twoavy\EvaluationTool\Models\EvaluationToolSurvey;

class EvaluationToolSurveyController extends Controller
{
    public function index(): JsonResponse
    {
        $surveys = EvaluationToolSurvey::all();
        return response()->json($surveys);
    }
}
