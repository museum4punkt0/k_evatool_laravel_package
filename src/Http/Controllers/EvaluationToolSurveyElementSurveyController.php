<?php

namespace Twoavy\EvaluationTool\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Twoavy\EvaluationTool\Http\Requests\EvaluationToolSurveyElementStoreRequest;
use Twoavy\EvaluationTool\Models\EvaluationToolSurveyElement;
use Twoavy\EvaluationTool\Traits\EvaluationToolResponse;

class EvaluationToolSurveyElementSurveyController extends Controller
{
    use EvaluationToolResponse;

    public function __construct()
    {
        $this->middleware("auth:api");
    }

    /**
     * Retrieve a list of all survey elements
     *
     * @param EvaluationToolSurveyElement $surveyElement
     * @return JsonResponse
     */
    public function index(EvaluationToolSurveyElement $surveyElement): JsonResponse
    {
        $surveys = $surveyElement->surveys;
        return $this->showAll($surveys);
    }
}
