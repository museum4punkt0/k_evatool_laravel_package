<?php

namespace Twoavy\EvaluationTool\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Twoavy\EvaluationTool\Http\Requests\EvaluationToolSurveyStoreRequest;
use Twoavy\EvaluationTool\Models\EvaluationToolSurvey;

class EvaluationToolSurveyController extends Controller
{
    /**
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $surveys = EvaluationToolSurvey::all();
        return response()->json($surveys);
    }

    /**
     * @param EvaluationToolSurvey $survey
     * @return JsonResponse
     */
    public function show(EvaluationToolSurvey $survey): JsonResponse
    {
        return response()->json($survey);
    }

    /**
     * @param EvaluationToolSurveyStoreRequest $request
     * @return JsonResponse
     */
    public function store(EvaluationToolSurveyStoreRequest $request): JsonResponse
    {
        return response()->json($request->all());
    }
}
