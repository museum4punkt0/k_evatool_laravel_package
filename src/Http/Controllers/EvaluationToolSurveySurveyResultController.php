<?php

namespace Twoavy\EvaluationTool\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use StdClass;
use Twoavy\EvaluationTool\Models\EvaluationToolSurvey;
use Twoavy\EvaluationTool\Models\EvaluationToolSurveyLanguage;
use Twoavy\EvaluationTool\Models\EvaluationToolSurveyStep;
use Twoavy\EvaluationTool\Models\EvaluationToolSurveyStepResult;
use Twoavy\EvaluationTool\Traits\EvaluationToolResponse;
use Twoavy\EvaluationTool\Transformers\EvaluationToolSurveyStepResultCombinedTransformer;
use Twoavy\EvaluationTool\Transformers\EvaluationToolSurveyTransformer;

class EvaluationToolSurveySurveyResultController extends Controller
{
    use EvaluationToolResponse;

    const STAR_RATING_RESULT_RATING_KEY = 'rating';
    const BINARY_VALUE_KEY = 'value';
    const YAYNAY_VALUE_KEY = 'value';
    const EMOJI_MEANING_KEY = 'meaning';

    public function __construct()
    {
        $this->middleware("auth:api")->except(["index", "show", "store"]);

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

        if ($survey->published === false) {
            return $this->errorResponse("survey not avaiable", 409);
        }

        // set new uuid and apply to request if not supplied
        if (!$request->has("uuid")) {
            $uuid = $this->generateUuid();
            $request->request->add(["uuid" => $uuid]);
        }

        foreach ($surveySteps as $surveyStep) {
            $surveyStep->sampleResultPayload = $this->getSampleResultPayload($surveyStep);
        }

        $data = $this->showAll($surveySteps, 200, EvaluationToolSurveyStepResultCombinedTransformer::class, false, false);

        return response()->json([
            "uuid"   => $request->uuid,
            "survey" => $this->transformData($survey, EvaluationToolSurveyTransformer::class, true),
            "steps"  => $data,
        ]);
    }

    public function store(EvaluationToolSurvey $survey, Request $request)
    {
        if (!$request->has("surveyStepId")) {
            return $this->errorResponse("no survey step id provided", 409);
        }

        if (!$surveyStep = EvaluationToolSurveyStep::find($request->surveyStepId)) {
            return $this->errorResponse("survey step does not exist", 409);
        }

        if ($survey->id !== $surveyStep->survey_id) {
            return $this->errorResponse("survey ids do not match", 409);
        }

        if (!$request->has("uuid")) {
            return $this->errorResponse("no uuid provided", 409);
        }

        $surveyStepResult                     = new EvaluationToolSurveyStepResult();
        $surveyStepResult->survey_step_id     = $request->surveyStepId;
        $surveyStepResult->session_id         = $request->uuid;
        $surveyStepResult->result_value       = $request->resultValue;
        $surveyStepResult->result_language_id = $request->languageId;
        $surveyStepResult->params             = $surveyStep->survey_element->params;
        $surveyStepResult->answered_at        = Carbon::now();
        $surveyStepResult->save();
        return $this->showOne($surveyStepResult);
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
        $starRatingPayload                                        = new StdClass();
        $starRatingPayload->{self::STAR_RATING_RESULT_RATING_KEY} = 0;
        return $starRatingPayload;
    }

    public function samplePayloadMultipleChoice($params): StdClass
    {
        $multipleChoicePayload = new StdClass();
        return $multipleChoicePayload;
    }

    public function samplePayloadEmoji($params): StdClass
    {
        $emojiPayload                            = new StdClass();
        $emojiPayload->{self::EMOJI_MEANING_KEY} = "";
        return $emojiPayload;
    }

    public function samplePayloadSimpleText($params): StdClass
    {
        $simpleTextPayload = new StdClass();
        return $simpleTextPayload;
    }

    public function samplePayloadVideo($params): StdClass
    {
        $videoPayload = new StdClass();
        return $videoPayload;
    }

    public function samplePayloadBinary($params): StdClass
    {
        $binaryPayload                           = new StdClass();
        $binaryPayload->{self::BINARY_VALUE_KEY} = "";
        return $binaryPayload;
    }

    public function samplePayloadYayNay($params): StdClass
    {
        $yayNayPayload                           = new StdClass();
        $yayNayPayload->{self::YAYNAY_VALUE_KEY} = "";
        return $yayNayPayload;
    }

    public function rulesPayloadYayNay($params)
    {
        return $params;
    }
}
