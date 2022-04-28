<?php

namespace Twoavy\EvaluationTool\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use StdClass;
use Twoavy\EvaluationTool\Http\Requests\EvaluationToolSurveyRunIndexRequest;
use Twoavy\EvaluationTool\Http\Requests\EvaluationToolSurveyRunSurveyPathRequest;
use Twoavy\EvaluationTool\Http\Requests\EvaluationToolSurveyStepResultAssetStoreRequest;
use Twoavy\EvaluationTool\Models\EvaluationToolSurvey;
use Twoavy\EvaluationTool\Models\EvaluationToolSurveyLanguage;
use Twoavy\EvaluationTool\Models\EvaluationToolSurveyStep;
use Twoavy\EvaluationTool\Models\EvaluationToolSurveyStepResult;
use Twoavy\EvaluationTool\Models\EvaluationToolSurveyStepResultAsset;
use Twoavy\EvaluationTool\SurveyElementTypes\EvaluationToolSurveyElementTypeBinary;
use Twoavy\EvaluationTool\SurveyElementTypes\EvaluationToolSurveyElementTypeEmoji;
use Twoavy\EvaluationTool\SurveyElementTypes\EvaluationToolSurveyElementTypeMultipleChoice;
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

        $this->isDemo = false;
        if (request()->hasHeader('X-Demo')) {
            $this->isDemo = true;
        }

        $this->doneCount      = 0;
        $this->remainingCount = 0;
    }

    /**
     * @param $surveySlug
     * @param EvaluationToolSurveyRunIndexRequest $request
     * @return JsonResponse
     */
    public function index($surveySlug, EvaluationToolSurveyRunIndexRequest $request): JsonResponse
    {
        // tell the request that it is in "run mode"
        $request->request->add(["is_run" => true]);

        if (!$survey = EvaluationToolSurvey::where("slug", $surveySlug)->first()) {
            return $this->errorResponse("survey not found", 409);
        }

        $surveySteps = $survey->survey_steps->filter(function ($value) {
            return is_null($value->parent_step_id);
        });

        // only show if survey is published or in demo mode
        if (!$survey->published && !$this->isDemo) {
            return $this->errorResponse("survey not available", 410);
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

    public function indexStep($stepSlug, EvaluationToolSurveyRunIndexRequest $request): JsonResponse
    {
        $request->request->add(["is_run" => true]);

        // load all steps with slug (is always just one)
        if (!$step = EvaluationToolSurveyStep::where("slug", $stepSlug)->first()) {
            return $this->errorResponse("step not found", 409);
        }

        if (!in_array($step->survey_element->survey_element_type->key, EvaluationToolSurveyStep::SINGLE_STEP_ELEMENT_TYPES)) {
            return $this->errorResponse("step cannot be loaded with single access", 409);
        }

        // get survey from first step
        $survey = $step->survey;

        // prohibit direct access, when single step access is not set in survey
        if (!$survey->single_step_access) {
            return $this->errorResponse("step cannot be accessed directly", 409);
        }

        // only show if survey is published or in demo mode
        if (!$survey->published && !$this->isDemo) {
            return $this->errorResponse("survey not available", 410);
        }

        // set new uuid and apply to request if not supplied
        if (!$request->has("uuid")) {
            $uuid = $this->generateUuid();
            $request->request->add(["uuid" => $uuid]);
        } else {
            $uuid = $request->uuid;
        }


        $step->sampleResultPayload = $this->getSampleResultPayload($step);

        $resultsByUuid      = $this->getResultsByUuid($step, $uuid);
        $step->resultByUuid = $resultsByUuid->result;
        $step->isAnswered   = $resultsByUuid->isAnswered;

        $survey->status = [
            "currentStep"  => $step->isAnswered ? -1 : -2,
            "stepOrdering" => []
        ];

        $data = $this->showOne($step, 200, EvaluationToolSurveyStepResultCombinedTransformer::class, false, false);

        return response()->json([
            "uuid"   => $request->uuid,
            "survey" => $this->transformData($survey, EvaluationToolSurveyTransformer::class, true),
            "step"   => $data,
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

        if (!$survey->published && !$this->isDemo) {
            return $this->errorResponse("survey not available", 410);
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

        $deleteResult = false;
        // check if result shall be deleted
        if ($request->has("delete_result") && $request->delete_result) {
            $deleteResult = true;
        }

        if (!$surveyStepResult = EvaluationToolSurveyStepResult::where("session_id", $request->session_id)
            ->where("survey_step_id", $request->survey_step_id)
            ->when($surveyStep->survey_element_type->key === "video", function ($query) use ($request) {
                $query->where("time", $request->time);
            })
            ->first()) {
            // send error if no result but deleted param is sent
            if ($deleteResult) {
                return $this->errorResponse("no result found, but deleted parameter was send", 409);
            }
            $surveyStepResult = new EvaluationToolSurveyStepResult();
        } else {
            // video can store several results, but overwrite if result is at same timecode position
            if ($surveyStep->survey_element_type->key === "video") {
                // check if video result has timecode
                if (!$request->has("time")) {
                    return $this->errorResponse("video results must send a timecode (i.e. 00:00:02:25)", 409);
                }
                // delete records
                if ($deleteResult) {
                    $surveyStepResult->delete();
                    return $this->showOne($surveyStepResult->refresh());
                }
            } else {
                $surveyStepResult->changed_answer++;
            }
        }

        $resultValue = $request->result_value;
        if ($surveyStep->survey_element_type->key == "voiceInput") {

            // init result payload
            $resultPayload = [];

            // check if result value exists
            if ($surveyStepResult->result_value) {
                $resultPayload = $surveyStepResult->result_value;
            }

            // check if manual text exists
            if ($request->has("result_value.manual_text")) {
                $resultPayload["manual_text"] = $request->result_value["manual_text"];
            }
            $resultValue = $resultPayload;
        }


        $surveyStepResult->survey_step_id     = $request->survey_step_id;
        $surveyStepResult->session_id         = $request->session_id;
        $surveyStepResult->result_value       = $resultValue;
        $surveyStepResult->time               = $request->time;
        $surveyStepResult->result_language_id = $language->id;
        $surveyStepResult->params             = $surveyStep->survey_element->params;
        $surveyStepResult->answered_at        = Carbon::now();

        $surveyStepResult->save();

        if ($deleteResult) {
            $surveyStepResult->delete();
        }

        // store audio asset
        if ($surveyStep->survey_element_type->key == "voiceInput") {
            if (isset($request->result_value)) {
                if (isset($request->result_value["audio"])) {
                    $this->createAudioAsset($request->result_value["audio"], $surveyStepResult);
                }
            }
        }

        return $this->showOne($surveyStepResult->refresh());
    }

    /**
     * @param $stepSlug
     * @param EvaluationToolSurveySurveyStepResultStoreRequest $request
     * @return JsonResponse
     */
    public function storeStep($stepSlug, EvaluationToolSurveySurveyStepResultStoreRequest $request): JsonResponse
    {
        // return error if no session id provided
        if (!$request->has("session_id")) {
            return $this->errorResponse("no session id (uuid) provided", 409);
        }

        // get step and return error if not found
        if (!$step = EvaluationToolSurveyStep::where("slug", $stepSlug)->first()) {
            return $this->errorResponse("step not found", 409);
        }

        if (!in_array($step->survey_element->survey_element_type->key, EvaluationToolSurveyStep::SINGLE_STEP_ELEMENT_TYPES)) {
            return $this->errorResponse("step cannot be loaded with single access", 409);
        }

        // get survey from first step
        $survey = $step->survey;

        // prohibit direct access, when single step access is not set in survey
        if (!$survey->single_step_access) {
            return $this->errorResponse("step cannot be accessed directly", 409);
        }

        // only show if survey is published or in demo mode
        if (!$survey->published && !$this->isDemo) {
            return $this->errorResponse("survey not available", 410);
        }

        // check for existing answer
        if (!$this->checkPreviousStepAnswer($step, $request->session_id)) {
            return $this->errorResponse("survey result cannot be stored", 409);
        }

        // get language
        $language = EvaluationToolSurveyLanguage::where("code", $request->result_language)->first();

        // load existing result or create new one
        if (!$surveyStepResult = EvaluationToolSurveyStepResult::where("session_id", $request->session_id)
            ->where("survey_step_id", $request->survey_step_id)
            ->first()) {
            $surveyStepResult = new EvaluationToolSurveyStepResult();
        }

        // set all relevant keys
        $surveyStepResult->survey_step_id     = $request->survey_step_id;
        $surveyStepResult->session_id         = $request->session_id;
        $surveyStepResult->result_value       = $request->result_value;
        $surveyStepResult->time               = $request->time;
        $surveyStepResult->result_language_id = $language->id;
        $surveyStepResult->params             = $step->survey_element->params;
        $surveyStepResult->answered_at        = Carbon::now();

        // save result
        $surveyStepResult->save();

        // store audio asset
        if ($step->survey_element_type->key == "voiceInput") {
            if (isset($request->result_value)) {
                if (isset($request->result_value["audio"])) {
                    $this->createAudioAsset($request->result_value["audio"], $surveyStepResult);
                }
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
        // Todo: Check if previous step was answered correctly
        /*if (!$previousStep = EvaluationToolSurveyStep::where("next_step_id", $surveyStep->id)
            ->first()) {
            return true;
        }

        if (!$previousStep->allow_skip && !EvaluationToolSurveyStepResult::where([
                "survey_step_id" => $previousStep->id,
                "session_id"     => $sessionId
            ])->first()) {
            return false;
        }*/

        return true;
    }

    /**
     * Retrieve the survey paths possible
     *
     * @param $surveySlug
     * @param EvaluationToolSurveyRunSurveyPathRequest $request
     * @return JsonResponse
     */
    public function getSurveyPath($surveySlug, EvaluationToolSurveyRunSurveyPathRequest $request): JsonResponse
    {
        if (!$survey = EvaluationToolSurvey::where("slug", $surveySlug)->first()) {
            return $this->errorResponse("survey not found", 409);
        }

        $results = null;

        if ($request->has("uuid")) {
            $uuid    = $request->uuid;
            $results = EvaluationToolSurveyStepResult::where("session_id", $uuid)->whereIn("survey_step_id", $survey->survey_steps->pluck("id"))->orderBy("answered_at", "ASC")->get();
        }

        $path         = new StdClass;
        $firstStep    = $survey->survey_steps->where("is_first_step")->first();
        $path->stepId = $firstStep->id;

        // check of step has results and label as "done"
        $done      = false;
        $remaining = false;

        if ($results->first() && $results->first()->survey_step_id == $firstStep->id) {
            $path->done = true;
            $done       = true;
            $this->doneCount++;

            if ($results->count() == 1) {
                $lastResult = $results->first();
            }

            // remove the first result
            $results->shift();
            if ($results->count() === 0) {
                $path->lastDone = true;
                $remaining      = true;
                $path->ended    = $this->checkLastResultForEnd($lastResult, $firstStep);
            }
        }

        $path->children = $this->followPath($firstStep->id, $survey, $results, $done, $remaining);

        $response                 = new StdClass;
        $response->doneCount      = $this->doneCount;
        $response->remainingCount = $this->remainingCount;
//        $response->maxCount       = $this->getPathMaximumDepth($path);
        $response->path = $path;

        return $this->successResponse($response);
    }

    public function followPath($stepId, $survey, $results, $stepIsDone = false, $remaining = false): array
    {
        $step        = $survey->survey_steps->find($stepId);
        $element     = $step->survey_element;
        $elementType = $element->survey_element_type->key;

        $pathParts = [];

        if ($step->next_step_id) {
            $pathParts[] = $survey->survey_steps->find($step->next_step_id)->id;
        }

        if ($step->result_based_next_steps) {

            // the same array merge works on all element types. yet they are split here in case they need to be handled individually
            if ($elementType == "emoji") {
                $pathParts = array_merge($pathParts, collect($step->result_based_next_steps)->pluck("stepId")->toArray());
            }
            if ($elementType == "multipleChoice") {
                $pathParts = array_merge($pathParts, collect($step->result_based_next_steps)->pluck("stepId")->toArray());
            }
            if ($elementType == "binary") {
                $pathParts = array_merge($pathParts, collect($step->result_based_next_steps)->pluck("stepId")->toArray());
            }
            if ($elementType == "starRating") {
                $pathParts = array_merge($pathParts, collect($step->result_based_next_steps)->pluck("stepId")->toArray());
            }
        }

        // array of elements that shall be amended to the path
        $pathAmend = [];

        if (!empty($pathParts)) {
            foreach ($pathParts as $pathPart) {
                $subPath         = new StdClass;
                $subPath->stepId = $pathPart;

                // check if there are (still) results and previous step is done
                $done = $stepIsDone;
                if ($results->count() > 0 && $stepIsDone) {

                    // check of step has results and label as "done"
                    if ($results->first()->survey_step_id == $pathPart) {
                        $subPath->done = true;
                        $done          = true;
                        $this->doneCount++;

                        if ($results->count() == 1) {
                            $lastResult = $results->first();
                        }

                        // remove the first result
                        $results->shift();
                        if ($results->count() == 0) {
                            $subPath->lastDone = true;
                            $remaining         = true;
                            $subPath->ended    = $this->checkLastResultForEnd($lastResult, $survey->survey_steps->find($pathPart));
                        }
                    }
                }

                // keep following the path recursively
                if ($children = $this->followPath($pathPart, $survey, $results, $done, $remaining)) {
                    $subPath->children = $children;
                }

                $pathAmend[] = $subPath;
            }
        }

        return $pathAmend;
    }

    public function checkLastResultForEnd($result, $step): bool
    {
        $elementType = $step->survey_element_type->key;

        if (!$step->next_step_id) {
            if (!$step->result_based_next_steps) {
                return true;
            } else {
                if ($elementType == "emoji") {
                    return EvaluationToolSurveyElementTypeEmoji::isResultBasedMatch($result, $step);
                }

                if ($elementType == "binary") {
                    return EvaluationToolSurveyElementTypeBinary::isResultBasedMatch($result, $step);
                }

                if ($elementType == "starRating") {
                    return EvaluationToolSurveyElementTypeStarRating::isResultBasedMatch($result, $step);
                }

                if ($elementType == "multipleChoice") {
                    return EvaluationToolSurveyElementTypeMultipleChoice::isResultBasedMatch($result, $step);
                }
            }
        }
        return false;
    }

    public function getPathMaximumDepth($path)
    {
        return $this->walkPath($path->children);
    }

    public function walkPath($path, $depth = 1)
    {
        foreach ($path as $subPath) {
            if (isset($subPath->children)) {
                $depth++;
                $newDepth = $this->walkPath($subPath->children, $depth);
//                echo $newDepth . "-" . $depth . PHP_EOL;
                if ($newDepth == $depth) {
//                    $depth--;
                }
            }
        }
        return $depth;
    }

    public function createAudioAsset($audioData, EvaluationToolSurveyStepResult $result)
    {
        $audioData       = str_replace('data:audio/wav;base64,', '', $audioData);
        $hash            = substr(md5($audioData), 0, 6);
        $filenameBase    = "recording_" . date('ymd_His') . "_" . $hash;
        $filenameInterim = $filenameBase . "_interim.wav";
        $filename        = $filenameBase . ".mp3";

        // store interim file
        $this->audioDisk->put($filenameInterim, base64_decode($audioData));

        // get paths
        $sourcePath = $this->audioDisk->path($filenameInterim);
        $targetPath = $this->audioDisk->path($filename);

        // convert file to mp3
        $command = "ffmpeg -i " . $sourcePath . " -vn -b:a 128k " . $targetPath;
        exec($command);

        // delete interim file
        $this->audioDisk->delete($filenameInterim);

        //get file hash
        $fileHash = hash_file('md5', $this->audioDisk->path($filename));

        // create file
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

        // init result payload
        $resultPayload                = new StdClass();
        $resultPayload->resultAssedId = $resultAsset->id;

        // check if result value exists
        if ($result->result_value) {
            $resultPayload = $result->result_value;
        }

        // check if manual text exists
        if (request()->has("manual_text")) {
            $resultPayload->manual_text = request()->result_value["manual_text"];
        }
        $resultValue = $resultPayload;

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

        (new EvaluationToolSurveyStepResultAssetController)->createStepResultAsset($request->audio, $request->surveyStepResultId);

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
                if (!empty($upcomingStep->result_based_next_steps)) {
                    if ($upcomingStep->isAnswered) {
                        $nextStep = $this->getResultBasedNextStep($upcomingStep);
                        if ($nextStep && $nextSurveyStep = $surveySteps->find($nextStep->id)) {
                            $currentStep    = $nextSurveyStep;
                            $stepOrdering[] = $nextSurveyStep->id;
                            $upcomingStep   = $nextSurveyStep;
                            break;
                        }
                    }
                }

                if ($upcomingStep->next_step_id == $surveyStep->id) {
                    if ($upcomingStep->isAnswered && !$hasUnansweredStep) {
                        $currentStep = $surveyStep;
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

//        echo "current " . $currentStep->id . " finished " . $finished . PHP_EOL;

        return [
            "currentStep"  => $finished ? -1 : $currentStep->id,
            "stepOrdering" => $stepOrdering
        ];
    }

    public function getResultBasedNextStep($surveyStep)
    {
        switch ($surveyStep->survey_element->survey_element_type->key) {
            case "binary":
                $stepId = EvaluationToolSurveyElementTypeBinary::getResultBasedNextStep($surveyStep);
                break;
            case "starRating":
                $stepId = EvaluationToolSurveyElementTypeStarRating::getResultBasedNextStep($surveyStep);
                break;
            case "emoji":
                $stepId = EvaluationToolSurveyElementTypeEmoji::getResultBasedNextStep($surveyStep);
                break;
            case "multipleChoice":
                $stepId = EvaluationToolSurveyElementTypeMultipleChoice::getResultBasedNextStep($surveyStep);
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
            if ($surveyStep->survey_step_result_by_uuid($uuid)->get()->count() > 0) {
                $statusByUuid->result     = $surveyStep->survey_step_result_by_uuid($uuid)->get()->map(function ($result) {
                    return [
                        "timecode"    => $result->time,
                        "resultValue" => $result->result_value
                    ];
                });
                $statusByUuid->isAnswered = true;
            }
        } else {
            if ($surveyStep->survey_step_result_by_uuid($uuid)->get()->count() > 0) {
                $statusByUuid->result     = $surveyStep->survey_step_result_by_uuid($uuid)->get()->pluck("result_value")->first();
                $statusByUuid->isAnswered = true;
            }
        }
        return $statusByUuid;
    }
}
