<?php

namespace Twoavy\EvaluationTool\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use StdClass;
use Twoavy\EvaluationTool\Http\Requests\EvaluationToolSurveyStepResultAssetStoreRequest;
use Twoavy\EvaluationTool\Models\EvaluationToolSurvey;
use Twoavy\EvaluationTool\Models\EvaluationToolSurveyLanguage;
use Twoavy\EvaluationTool\Models\EvaluationToolSurveyStep;
use Twoavy\EvaluationTool\Models\EvaluationToolSurveyStepResult;
use Twoavy\EvaluationTool\Models\EvaluationToolSurveyStepResultAsset;
use Twoavy\EvaluationTool\SurveyElementTypes\EvaluationToolSurveyElementTypeStarRating;
use Twoavy\EvaluationTool\Traits\EvaluationToolResponse;
use Twoavy\EvaluationTool\Transformers\EvaluationToolSurveyStepResultCombinedTransformer;
use Twoavy\EvaluationTool\Http\Requests\EvaluationToolSurveySurveyStepResultStoreRequest;
use Twoavy\EvaluationTool\Transformers\EvaluationToolSurveyTransformer;

class EvaluationToolSurveySurveyRunController extends Controller
{
    use EvaluationToolResponse;

    const STAR_RATING_RESULT_RATING_KEY = 'rating';
    const BINARY_VALUE_KEY = 'value';
    const YAYNAY_VALUE_KEY = 'value';
    const TEXTINPUT_VALUE_KEY = 'value';
    const VOICEINPUT_VALUE_KEY = 'value';
    const EMOJI_MEANING_KEY = 'meaning';

    public function __construct()
    {
        $this->defaultLanguage = EvaluationToolSurveyLanguage::where("default", true)->first();
        $this->audioDisk       = Storage::disk("evaluation_tool_audio");
    }

    /**
     * @param $surveySlug
     * @param Request $request
     * @return JsonResponse
     */
    public function index($surveySlug, Request $request): JsonResponse
    {
        // tell the request that it is in "run mode"
        $request->request->add(["is_run" => true]);

        if (!$survey = EvaluationToolSurvey::where("slug", $surveySlug)->first()) {
            return $this->errorResponse("survey not found", 409);
        }

        $surveySteps = $survey->survey_steps->filter(function ($value) {
            return is_null($value->parent_step_id);
        });;

        if (!$survey->published) {
            return $this->errorResponse("survey not avaiable", 409);
        }

        // set new uuid and apply to request if not supplied
        if (!$request->has("uuid")) {
            $uuid = $this->generateUuid();
            $request->request->add(["uuid" => $uuid]);
        } else {
            $uuid = $request->uuid;
        }

        foreach ($surveySteps as $surveyStep) {
            $surveyStep->sampleResultPayload = $this->getSampleResultPayload($surveyStep);

            $resultsByUuid            = $this->getResultsByUuid($surveyStep, $uuid);
            $surveyStep->resultByUuid = $resultsByUuid->result;
            $surveyStep->isAnswered   = $resultsByUuid->isAnswered;
        }

        $survey->status = $this->getPositionWithinSurvey($surveySteps);

        $data = $this->showAll($surveySteps, 200, EvaluationToolSurveyStepResultCombinedTransformer::class, false, false);

        return response()->json([
            "uuid"   => $request->uuid,
            "survey" => $this->transformData($survey, EvaluationToolSurveyTransformer::class, true),
            "steps"  => $data,
        ]);
    }

    /**
     * @param $surveySlug
     * @param EvaluationToolSurveySurveyStepResultStoreRequest $request
     * @return JsonResponse
     */
    public function store($surveySlug, EvaluationToolSurveySurveyStepResultStoreRequest $request): JsonResponse
    {
        if (!$survey = EvaluationToolSurvey::where("slug", $surveySlug)->first()) {
            return $this->errorResponse("survey not found", 409);
        }

        // Todo: Add resubmit counter (observer)
        if (!$surveyStep = EvaluationToolSurveyStep::find($request->survey_step_id)) {
            return $this->errorResponse("survey step does not exist", 409);
        }

        if ($survey->id !== $surveyStep->survey_id) {
            return $this->errorResponse("survey ids do not match", 409);
        }

        if (!$request->has("session_id")) {
            return $this->errorResponse("no session id (uuid) provided", 409);
        }

        if (!$this->checkPreviousStepAnswer($surveyStep, $request->session_id)) {
            return $this->errorResponse("survey result cannot be stored", 409);
        }

        $language = EvaluationToolSurveyLanguage::where("code", $request->result_language)->first();

        if (!$surveyStepResult = EvaluationToolSurveyStepResult::where("session_id", $request->session_id)
            ->where("survey_step_id", $request->survey_step_id)
            ->first()) {
            $surveyStepResult = new EvaluationToolSurveyStepResult();
        } else {
            // video can store several results, but overwrite if result is at same timecode position
            if ($surveyStep->survey_element_type->key === "video") {
                // check if video result has timecode
                if (!$request->has("time")) {
                    return $this->errorResponse("video results must send a timecode (i.e. 00:00:02:25)", 409);
                }
                if (!$surveyStepResult = EvaluationToolSurveyStepResult::where("session_id", $request->session_id)
                    ->where("survey_step_id", $request->survey_step_id)
                    ->where("time", $request->time)
                    ->first()) {
                    $surveyStepResult = new EvaluationToolSurveyStepResult();
                } else {
                    $surveyStepResult->changed_answer++;
                }
            } else {
                $surveyStepResult->changed_answer++;
            }
        }

        $surveyStepResult->survey_step_id     = $request->survey_step_id;
        $surveyStepResult->session_id         = $request->session_id;
        $surveyStepResult->result_value       = $request->result_value;
        $surveyStepResult->time               = $request->time;
        $surveyStepResult->result_language_id = $language->id;
        $surveyStepResult->params             = $surveyStep->survey_element->params;
        $surveyStepResult->answered_at        = Carbon::now();

        $surveyStepResult->save();

        // store audio asset
        if ($surveyStep->survey_element_type->key == "voiceInput") {
            if (isset($request->result_value)) {
                $this->createAudioAsset($request->result_value["audio"], $surveyStepResult);
            }
        }

        return $this->showOne($surveyStepResult->refresh());
    }

    /**
     * @param EvaluationToolSurveyStep $surveyStep
     * @param $sessionId
     * @return bool
     */
    function checkPreviousStepAnswer(EvaluationToolSurveyStep $surveyStep, $sessionId): bool
    {
        if (!$previousStep = EvaluationToolSurveyStep::where("next_step_id", $surveyStep->id)
            ->first()) {
            return true;
        }

        if (!$previousStep->allow_skip && !EvaluationToolSurveyStepResult::where([
                "survey_step_id" => $previousStep->id,
                "session_id"     => $sessionId
            ])->first()) {
            return false;
        }


        return true;
    }

    public function createAudioAsset($audioData, EvaluationToolSurveyStepResult $result)
    {
        $audioData = str_replace('data:audio/wav;base64,', '', $audioData);
        $hash      = substr(md5($audioData), 0, 6);
        $filename  = "recording_" . date('ymd_His') . "_" . $hash . ".wav";

        // store file
        $this->audioDisk->put($filename, base64_decode($audioData));

        $fileHash = hash_file('md5', $this->audioDisk->path($filename));

        if (!$resultAsset = EvaluationToolSurveyStepResultAsset::where("hash", $fileHash)->first()) {
            $resultAsset                        = new EvaluationToolSurveyStepResultAsset();
            $resultAsset->filename              = $filename;
            $resultAsset->hash                  = hash_file('md5', $this->audioDisk->path($filename));
            $resultAsset->mime                  = mime_content_type($this->audioDisk->path($filename));
            $resultAsset->size                  = $this->audioDisk->size($filename);
            $resultAsset->meta                  = EvaluationToolAssetController::getFileMetaData($this->audioDisk->path($filename));
            $resultAsset->survey_step_result_id = $result->id;
            $resultAsset->save();
        }

        $resultValue          = ["resultAssetId" => $resultAsset->id];
        $result->result_value = $resultValue;
        $result->save();
    }

    /**
     * Stores a survey step result asset record
     *
     * @param $surveySlug
     * @param EvaluationToolSurveyStepResultAssetStoreRequest $request
     * @return JsonResponse
     */
    public function storeAsset($surveySlug, EvaluationToolSurveyStepResultAssetStoreRequest $request): JsonResponse
    {
        if (!$survey = EvaluationToolSurvey::where("slug", $surveySlug)->first()) {
            return $this->errorResponse("survey not found", 409);
        }

        // Todo: Create Request
        if (!$request->has("audio")) {
            return $this->errorResponse("no audio provided", 409);
        }

        if (!$request->has("surveyStepResultId")) {
            return $this->errorResponse("no survey step result id provided", 409);
        }

        /*if (!$result = EvaluationToolSurveyStepResult::find($request->surveyStepResultId)) {
            return $this->errorResponse("survey step result not found", 409);
        }*/

        $fileContent = $request->audio;
        $fileContent = str_replace('data:audio/wav;base64,', '', $fileContent);
        $hash        = substr(md5($fileContent), 0, 6);
        $filename    = "recording_" . date('ymd_His') . "_" . $hash . ".wav";

        // store file
        $this->audioDisk->put($filename, base64_decode($fileContent));

        $resultAsset                        = new EvaluationToolSurveyStepResultAsset();
        $resultAsset->filename              = $filename;
        $resultAsset->hash                  = hash_file('md5', $this->audioDisk->path($filename));
        $resultAsset->mime                  = mime_content_type($this->audioDisk->path($filename));
        $resultAsset->size                  = $this->audioDisk->size($filename);
        $resultAsset->meta                  = EvaluationToolAssetController::getFileMetaData($this->audioDisk->path($filename));
        $resultAsset->survey_step_result_id = $request->surveyStepResultId;
        $resultAsset->save();

        return $this->showOne($resultAsset);
    }

    private function generateUuid(): UuidInterface
    {
        return Uuid::uuid4();
    }

    public function getSampleResultPayload(EvaluationToolSurveyStep $surveyStep): StdClass
    {
        $payload              = new StdClass;
        $payload->elementType = $surveyStep->survey_element->survey_element_type->key;

        $samplePayloadFunctionName           = 'samplePayload' . ucfirst($payload->elementType);
        $payload->resultData                 = new StdClass;
        $payload->resultData->resultValue    = $this->{$samplePayloadFunctionName}($surveyStep->survey_element->params);
        $payload->resultData->resultLanguage = $this->defaultLanguage->code;

        return $payload;
    }

    public function samplePayloadStarRating($params): StdClass
    {
        $starRatingPayload                                        = new StdClass();
        $starRatingPayload->{self::STAR_RATING_RESULT_RATING_KEY} = 0;
        return $starRatingPayload;
    }

    public function samplePayloadMultipleChoice($params): array
    {
        $numberOfOptions = $params->minSelectable;
        // return more option if possible
        if ($params->minSelectable < $params->maxSelectable) {
            $numberOfOptions = $params->maxSelectable - $params->minSelectable + 1;
        }

        $selected = [];
        $options  = $params->options;

        for ($i = 0; $i < $numberOfOptions; $i++) {
            $selected[] = $options[$i]->value;
        }

        return $selected;
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

    public function samplePayloadTextInput($params): StdClass
    {
        $textInputPayload                              = new StdClass();
        $textInputPayload->{self::TEXTINPUT_VALUE_KEY} = "";
        return $textInputPayload;
    }

    public function samplePayloadVoiceInput($params): StdClass
    {
        // TODO
        $voiceInputPayload                               = new StdClass();
        $voiceInputPayload->{self::VOICEINPUT_VALUE_KEY} = "";
        return $voiceInputPayload;
    }

    public function rulesPayloadYayNay($params)
    {
        return $params;
    }

    public function getPositionWithinSurvey($surveySteps): ?array
    {
        $stepOrdering = [];

        if (!$firstStep = $surveySteps->whereNotNull("is_first_step")->first()) {
            return null;
        }

        $upcomingStep      = $firstStep;
        $currentStep       = $firstStep;
        $stepOrdering[]    = $firstStep->id;
        $hasUnansweredStep = false;
        $finished          = false;

        foreach ($surveySteps as $surveyStepMain) {
            foreach ($surveySteps as $surveyStep) {
                if ($upcomingStep->next_step_id == $surveyStep->id) {
                    if ($upcomingStep->isAnswered && !$hasUnansweredStep) {
                        // Todo check result based next steps
                        if ($upcomingStep->result_based_next_steps) {
                            $currentStep = $this->getResultBasedNextStep($upcomingStep);
                        } else {
                            $currentStep = $surveyStep;
                        }
                    } else {
                        $hasUnansweredStep = true;
                    }

                    $stepOrdering[] = $surveyStep->id;
                    $upcomingStep   = $surveyStep;
                    break;
                }
            }
        }

        // check if current step is last step and answered
        if ($currentStep->isAnswered && $currentStep->id == end($stepOrdering)) {
            $finished = true;
        }

        // if only one step exists (excluding time based steps)
        if ($surveySteps->count() == 1) {
            if ($upcomingStep->isAnswered) {
                $finished = true;
            }
        }

        return [
            "currentStep"  => $finished ? -1 : $currentStep->id,
            "stepOrdering" => $stepOrdering
        ];
    }

    public function getResultBasedNextStep($surveyStep)
    {
        switch ($surveyStep->survey_element->survey_element_type->key) {
            case "binary":
//                EvaluationToolSurveyElementTypeBinary::getResultBasedNextStep($surveyStep);
                break;
            case "starRating":
                $stepId = EvaluationToolSurveyElementTypeStarRating::getResultBasedNextStep($surveyStep);
                break;
            default:
                break;
        }

        if (isset($stepId)) {
            return EvaluationToolSurveyStep::find($stepId);
        }

        if ($surveyStep->next_step_id) {
            return EvaluationToolSurveyStep::find($surveyStep->next_step_id);
        }

        return null;
    }

    public function getResultsByUuid(EvaluationToolSurveyStep $surveyStep, $uuid): StdClass
    {
        $statusByUuid             = new StdClass;
        $statusByUuid->isAnswered = false;
        $statusByUuid->result     = null;

        if ($surveyStep->survey_element_type->key === "video") {
            if ($surveyStep->survey_step_result_by_uuid($uuid)->count() > 0) {
                $statusByUuid->result     = $surveyStep->survey_step_result_by_uuid($uuid)->map(function ($result) {
                    return [
                        "timecode"    => $result->time,
                        "resultValue" => $result->result_value
                    ];
                });
                $statusByUuid->isAnswered = true;
            }

        } else {
            if ($surveyStep->survey_step_result_by_uuid($uuid)->count() > 0) {
                $statusByUuid->result     = $surveyStep->survey_step_result_by_uuid($uuid)->pluck("result_value")->first();
                $statusByUuid->isAnswered = true;
            }
        }
        return $statusByUuid;
    }
}
