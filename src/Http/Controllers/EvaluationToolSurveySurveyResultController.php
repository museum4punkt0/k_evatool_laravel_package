<?php

namespace Twoavy\EvaluationTool\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use StdClass;
use Twoavy\EvaluationTool\Http\Requests\EvaluationToolSurveyStepStoreRequest;
use Twoavy\EvaluationTool\Models\EvaluationToolSurvey;
use Twoavy\EvaluationTool\Models\EvaluationToolSurveyLanguage;
use Twoavy\EvaluationTool\Models\EvaluationToolSurveyStep;
use Twoavy\EvaluationTool\Traits\EvaluationToolResponse;
use Twoavy\EvaluationTool\Transformers\EvaluationToolSurveyStepResultCombinedTransformer;

class EvaluationToolSurveySurveyResultController extends Controller
{
    use EvaluationToolResponse;

    const STAR_RATING_RESULT_RATING_KEY = 'rating';

    public function __construct()
    {
        $this->middleware("auth:api")->except(["index", "show"]);

        $this->defaultLanguage = EvaluationToolSurveyLanguage::where("default", true)->first();
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
            $surveyStep->sampleResultPayload = $this->getSampleResultPayload($surveyStep);
        }

        $data = $this->showAll($surveySteps, 200, EvaluationToolSurveyStepResultCombinedTransformer::class, false, false);

        return response()->json(["uuid" => $request->uuid, "steps" => $data]);
    }

    public function store(EvaluationToolSurvey $survey, EvaluationToolSurveyStep $surveyStep, Request $request)
    {
        if ($survey->id !== $surveyStep->survey_id) {
            $this->errorResponse("survey ids do not match", 409);
        }

        if (!$request->has("uuid")) {
            $this->errorResponse("no uuid provided", 409);
        }
    }

    private function generateUuid(): UuidInterface
    {
        return Uuid::uuid4();
    }

    public function getSampleResultPayload(EvaluationToolSurveyStep $surveyStep): StdClass
    {
        $payload              = new StdClass;
        $payload->elementType = $surveyStep->survey_element->survey_element_type->key;

        $samplePayloadFunctionName        = 'samplePayload' . ucfirst($payload->elementType);
        $payload->resultData              = new StdClass;
        $payload->resultData->resultValue = $this->{$samplePayloadFunctionName}($surveyStep->survey_element->params);
        $payload->resultData->languageId  = $this->defaultLanguage->id;

        return $payload;
    }

    public function samplePayloadStarRating($params): StdClass
    {
        $starRatingPayload                                  = new StdClass();
        $starRatingPayload->{self::STAR_RATING_RESULT_RATING_KEY} = 0;
        return $starRatingPayload;
    }

    public function samplePayloadMultipleChoice($params)
    {
        return $params;
    }

    public function samplePayloadEmoji($params)
    {
        return $params;
    }

    public function samplePayloadSimpleText($params)
    {
        return $params;
    }

    public function samplePayloadVideo($params)
    {
        return $params;
    }

    public function samplePayloadBinary($params)
    {
        return $params;
    }

    public function samplePayloadYayNay($params)
    {
        return $params;
    }
}
