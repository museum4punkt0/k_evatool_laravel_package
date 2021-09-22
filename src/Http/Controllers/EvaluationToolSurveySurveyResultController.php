<?php

namespace Twoavy\EvaluationTool\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Twoavy\EvaluationTool\Http\Requests\EvaluationToolSurveyStepStoreRequest;
use Twoavy\EvaluationTool\Models\EvaluationToolSurvey;
use Twoavy\EvaluationTool\Models\EvaluationToolSurveyStep;
use Twoavy\EvaluationTool\Traits\EvaluationToolResponse;
use Twoavy\EvaluationTool\Transformers\EvaluationToolSurveyStepResultCombinedTransformer;

class EvaluationToolSurveySurveyResultController extends Controller
{
    use EvaluationToolResponse;

    public function __construct()
    {
        $this->middleware("auth:api")->except(["index", "show"]);
    }

    /**
     * @param EvaluationToolSurvey $survey
     * @param Request $request
     * @return JsonResponse
     */
    public function index(EvaluationToolSurvey $survey, Request $request): JsonResponse
    {
        $surveySteps = $survey->survey_steps;

        // set new uuid and apply to request if not supplied
        if (!$request->has("uuid")) {
            $uuid = $this->generateUuid();
            $request->request->add(["uuid" => $uuid]);
        }

        foreach ($surveySteps as $surveyStep) {
            $surveyStep->check = "go";
        }

        $data = $this->showAll($surveySteps, 200, EvaluationToolSurveyStepResultCombinedTransformer::class, false, false);

        return response()->json(["uuid" => $request->uuid, "steps" => $data]);
    }

    public function store(EvaluationToolSurvey $survey, Request $request)
    {

    }

    private function generateUuid(): UuidInterface
    {
        return Uuid::uuid4();
    }
}
