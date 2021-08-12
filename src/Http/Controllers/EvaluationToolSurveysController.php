<?php

namespace Twoavy\EvaluationTool\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Twoavy\EvaluationTool\Http\Requests\EvaluationToolSurveyStoreRequest;
use Twoavy\EvaluationTool\Models\EvaluationToolSurvey;

class EvaluationToolSurveysController extends Controller
{
    public function index(): JsonResponse
    {
        $surveys = EvaluationToolSurvey::all();
        return response()->json($surveys);
    }

    public function store(EvaluationToolSurveyStoreRequest $request): JsonResponse
    {
        return response()->json($request->all());
    }
}
