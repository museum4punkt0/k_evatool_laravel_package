<?php

namespace Twoavy\EvaluationTool\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Twoavy\EvaluationTool\Http\Requests\EvaluationToolSurveyStoreRequest;
use Twoavy\EvaluationTool\Models\EvaluationToolSurvey;
use Twoavy\EvaluationTool\Traits\EvaluationToolResponse;

class EvaluationToolSurveyController extends Controller
{
    use EvaluationToolResponse;

    /**
     * Retrieve a list of all surveys
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $surveys = EvaluationToolSurvey::all();
        return $this->showAll($surveys);
    }

    /**
     *  Retrieve a single survey
     *
     * @param EvaluationToolSurvey $survey
     * @return JsonResponse
     */
    public function show(EvaluationToolSurvey $survey): JsonResponse
    {
        return response()->json($survey);
    }

    /**
     *
     *
     * @param EvaluationToolSurveyStoreRequest $request
     * @return JsonResponse
     */
    public function store(EvaluationToolSurveyStoreRequest $request): JsonResponse
    {
        $survey = new EvaluationToolSurvey();
        $survey->fill($request->all());
        $survey->save();
        return response()->json($survey);
    }
}
