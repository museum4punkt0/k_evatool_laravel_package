<?php

namespace Twoavy\EvaluationTool\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Twoavy\EvaluationTool\Http\Requests\EvaluationToolSurveyStoreAdminLayoutRequest;
use Twoavy\EvaluationTool\Http\Requests\EvaluationToolSurveyStoreRequest;
use Twoavy\EvaluationTool\Models\EvaluationToolSurvey;
use Twoavy\EvaluationTool\Observers\EvaluationToolSurveyObserver;
use Twoavy\EvaluationTool\Traits\EvaluationToolResponse;

class EvaluationToolSurveySeedController extends Controller
{
    use EvaluationToolResponse;

    public function __construct()
    {
        $this->middleware("auth:api");
    }

    /**
     *  Seed a single survey
     *
     * @param EvaluationToolSurvey $survey
     * @return JsonResponse
     */
    public function seedResults(EvaluationToolSurvey $survey): JsonResponse
    {
        // TODO: SEEDING
        return $this->showOne($survey);
    }
}
