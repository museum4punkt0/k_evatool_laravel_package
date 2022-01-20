<?php

namespace Twoavy\EvaluationTool\Http\Controllers;

use App\Http\Controllers\Controller;
use DonatelloZa\RakePlus\RakePlus;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use stdClass;
use Twoavy\EvaluationTool\Helpers\EvaluationToolHelper;
use Twoavy\EvaluationTool\Http\Requests\EvaluationToolSurveyStatsIndexRequest;
use Twoavy\EvaluationTool\Models\EvaluationToolSurvey;
use Twoavy\EvaluationTool\Models\EvaluationToolSurveyLanguage;
use Twoavy\EvaluationTool\Models\EvaluationToolSurveyStatsCache;
use Twoavy\EvaluationTool\Models\EvaluationToolSurveyStep;
use Twoavy\EvaluationTool\Models\EvaluationToolSurveyStepResult;
use Twoavy\EvaluationTool\Traits\EvaluationToolResponse;

class EvaluationToolSurveyStatsController extends Controller
{
    use EvaluationToolResponse;

    public function __construct()
    {
        $this->middleware("auth:api");

        $this->timeSpans = [
            "today" => [
                "start" => Carbon::now()->startOfDay(),
                "end" => Carbon::now()->endOfDay(),
            ],
            "yesterday" => [
                "start" => Carbon::yesterday()->startOfDay(),
                "end" => Carbon::yesterday()->endOfDay(),
            ],
            "currentWeek" => [
                "start" => Carbon::now()->startOfWeek(),
                "end" => Carbon::now()->endOfWeek(),
            ],
            "lastWeek" => [
                "start" => Carbon::now()->subWeek()->startOfWeek(),
                "end" => Carbon::now()->subWeek()->endOfWeek(),
            ],
            "currentMonth" => [
                "start" => Carbon::now()->startOfMonth(),
                "end" => Carbon::now()->endOfMonth(),
            ],
            "lastMonth" => [
                "start" => Carbon::now()->subMonth()->startOfMonth(),
                "end" => Carbon::now()->subMonth()->endOfMonth()],
            "currentYear" => [
                "start" => Carbon::now()->startOfYear(),
                "end" => Carbon::now()->endOfYear(),
            ],
            "lastYear" => [
                "start" => Carbon::now()->subYear()->startOfYear(),
                "end" => Carbon::now()->subYear()->endOfYear(),
            ],
            "currentSevenDays" => [
                "start" => Carbon::now()->subWeek(),
                "end" => Carbon::now(),
            ],
            "previousSevenDays" => [
                "start" => Carbon::now()->subWeeks(),
                "end" => Carbon::now()->subWeeks(2),
            ],
        ];

        $this->cacheTimeSpans = [
            "today" => [
                "start" => Carbon::now()->startOfDay(),
                "end" => Carbon::now()->endOfDay(),
                "noCache" => true,
            ],
        ];
    }

    public function getCacheTimeSpans($survey)
    {

        if (EvaluationToolSurveyStepResult::whereIn("survey_step_id", $survey->survey_steps->pluck("id"))->count() > 0) {
            $results = EvaluationToolSurveyStepResult::whereIn("survey_step_id", $survey->survey_steps->pluck("id"));

            $firstDate = $results->clone()->orderBy("answered_at", "ASC")->first()->answered_at;
//        $lastDate  = $results->clone()->orderBy("answered_at", "DESC")->first()->answered_at;

            if ($firstDate < Carbon::today()->startOfDay()) {
                $this->cacheTimeSpans["week"] = [
                    "start" => Carbon::yesterday()->startOfWeek(),
                    "end" => Carbon::yesterday()->endOfDay(),
                ];
            }
            if ($firstDate < Carbon::yesterday()->startOfWeek()) {
                $this->cacheTimeSpans["month"] = [
                    "start" => Carbon::yesterday()->startOfWeek()->startOfMonth(),
                    "end" => Carbon::yesterday()->subWeek()->endOfWeek(),
                ];
            }

            $i = 1;
            while ($firstDate < Carbon::yesterday()->startOfWeek()->subMonths($i)->startOfMonth()) {
                $key = Carbon::yesterday()->startOfWeek()->subMonths($i)->startOfMonth()->format("Y-m");
                $this->cacheTimeSpans[$key] = [
                    "start" => Carbon::yesterday()->startOfWeek()->subMonths($i)->startOfMonth(),
                    "end" => Carbon::yesterday()->startOfWeek()->subMonths($i)->endOfMonth(),
                    "cachable" => true,
                ];
                $i++;
            }

            /*foreach ($this->cacheTimeSpans as $key => $timeSpan) {
        echo $key . ": " . $timeSpan["start"] . " - " . $timeSpan["end"] . PHP_EOL;
        }*/
        }
    }

    public function getStatsCache(EvaluationToolSurvey $survey): JsonResponse
    {
//        EvaluationToolSurveyStepResult::whereIn("survey_step_id", $survey->survey_steps->pluck("id"))->update(["cached" => false]);
        //        EvaluationToolSurveyStatsCache::where("survey_id", $survey->id)->delete();

        $results = EvaluationToolSurveyStepResult::whereIn("survey_step_id", $survey->survey_steps->pluck("id"))
            ->where("cached", false)
            ->orderBy('answered_at', 'ASC')->take(1000)
            ->get();

        $dateReminder = Carbon::now()->format("Y-m-d");
        foreach ($results->where("cached", false) as $r => $result) {
            if ($dateReminder !== $result->answered_at) {
                if (!$resultCache = EvaluationToolSurveyStatsCache::where("survey_id", $survey->id)
                    ->where("date", $result->answered_at->format("Y-m-d"))
                    ->first()
                ) {
                    $resultCache = new EvaluationToolSurveyStatsCache();
                    $resultCache->survey_id = $survey->id;
                    $resultCache->date = $result->answered_at;
                    $resultCache->results = [];
                }
            }

            $elementType = $result->survey_step->survey_element_type->key;

            $resultsResults = $resultCache->results;

            if (!array_search($result->survey_step_id, array_column($resultsResults, 'stepId'))) {
                $resultsResults[] = [
                    "stepId" => $result->survey_step_id,
                    "type" => $result->survey_step->survey_element_type->key,
                    "results" => [],
                ];
            }

            $key = array_search($result->survey_step_id, array_column($resultsResults, 'stepId'));

            $className = 'Twoavy\EvaluationTool\SurveyElementTypes\EvaluationToolSurveyElementType' . ucfirst($elementType);
            if (class_exists($className)) {
                if (method_exists($className, "statsCountResult")) {
                    $resultsResults[$key]["results"] = $className::statsCountResult($result, $resultsResults[$key]["results"]);
                }
            }

            $resultCache->results = $resultsResults;

            $resultCache->save();
            $result->cached = true;
            $result->save();

            $dateReminder = $result->answered_at->format("Y-m-d");
        }

        return $this->successResponse($results->count() . " results");
    }

    public function getStatsTrend(EvaluationToolSurvey $survey): JsonResponse
    {
        $demo = request()->has("demo") && request()->demo == true;


        $results = EvaluationToolSurveyStepResult::whereIn("survey_step_id",
            $survey->survey_steps
                ->pluck("id"))->where('demo', $demo);

        $currentSevenDays = $results->clone()->where("answered_at", ">", Carbon::now()->subDays(7)->startOfDay());
        $lastSevenDays = $results->clone()->where("answered_at", ">", Carbon::now()->subDays(14)->startOfDay())
            ->where("answered_at", "<", Carbon::now()->subDays(8)->endOfDay());

        $participantsTotal = $results->clone()->groupBy("session_id")->select("session_id")->get()->count();
        $participantsCurrentSevenDays = $currentSevenDays->clone()->groupBy("session_id")->select("session_id")->get()->count();
        $participantsLastSevenDays = $lastSevenDays->clone()->groupBy("session_id")->select("session_id")->get()->count();

        $completedSurveysTotal = $results->clone()->where("survey_finished", true)->get()->count();
        $completedSurveysCurrentSevenDays = $currentSevenDays->clone()->where("survey_finished", true)->get()->count();
        $completedSurveysLastSevenDays = $lastSevenDays->clone()->where("survey_finished", true)->get()->count();

        $answersTotal = $results->count();
        $answersCurrentSevenDays = $currentSevenDays->count();
        $answersLastSevenDays = $lastSevenDays->count();

        $statsTrend = new StdClass;
        $statsTrend->participants = new StdClass;
        $statsTrend->participants->total = $participantsTotal;
        $statsTrend->participants->currentSevenDays = $participantsCurrentSevenDays;
        $statsTrend->participants->lastSevenDays = $participantsLastSevenDays;

        $statsTrend->completedSurveys = new StdClass;
        $statsTrend->completedSurveys->total = $completedSurveysTotal;
        $statsTrend->completedSurveys->currentSevenDays = $completedSurveysCurrentSevenDays;
        $statsTrend->completedSurveys->lastSevenDays = $completedSurveysLastSevenDays;

        $statsTrend->answers = new StdClass;
        $statsTrend->answers->total = $answersTotal;
        $statsTrend->answers->currentSevenDays = $answersCurrentSevenDays;
        $statsTrend->answers->lastSevenDays = $answersLastSevenDays;

        return $this->successResponse($statsTrend);
    }

    public function getTextAnalysis($results)
    {
        $analysis = new StdClass;
        $languageCodes = array_keys($results["texts"]);
        foreach ($languageCodes as $languageCode) {
            $language = EvaluationToolSurveyLanguage::where('code', $languageCode)->first();
            $text = implode($results["texts"][$languageCode]);
            $hash = md5($text);
            // return Cache::remember("analysis_" . $hash, Carbon::now()->addHour(), function () use ($text, $analysis, $languageCode, $language) {
            $analysis->$languageCode = new StdClass;
            switch ($language->code) {
                case "de":
                    $rake = RakePlus::create($text, "de_DE");
                    break;
                case "en":
                    $rake = RakePlus::create($text, "en_US");
                    break;
                case "fr":
                    $rake = RakePlus::create($text, "fr_FR");
                    break;
                case "it":
                    $rake = RakePlus::create($text, "it_IT");
                    break;
                default:
                    $analysis->$languageCode->errors = ["sorry, there is no rake analysis available for this language"];

            }

            $phrases = $rake->sortByScore('desc')->scores();
            $keywords = $rake->keywords();

            $analysis->$languageCode->phrases = $phrases;
            $analysis->$languageCode->keywords = $keywords;
            // });
        }
        return $analysis;
    }

    public function getStatsByStep(EvaluationToolSurvey $survey, EvaluationToolSurveyStep $step, EvaluationToolSurveyStatsIndexRequest $request): JsonResponse
    {
        if ($step->survey_id !== $survey->id) {
            return $this->errorResponse("step not in survey", 409);
        }

        $resultQuery = EvaluationToolSurveyStepResult::where("survey_step_id", $step->id)->with(["language", "survey_step"]);
        if ($request->has("demo") && $request->demo == true) {
            $resultQuery->where("demo", true);
        } else {
            $resultQuery->where("demo", false);
        }
        $resultQuerySpan = $resultQuery->clone();
        // check for start date
        if ($request->has("start")) {
            $resultQuerySpan->where("answered_at", ">=", Carbon::createFromFormat("Y-m-d", $request->start)->startOfDay());
        }

        // check for end date
        if ($request->has("end")) {
            $resultQuerySpan->where("answered_at", "<=", Carbon::createFromFormat("Y-m-d", $request->end)->endOfDay());
        }

        $resultsSpan = $resultQuerySpan->get();
        $results = $resultQuery->get();

        $elementType = $step->survey_element_type->key;

        $this->getCacheTimeSpans($survey);

        $resultsPayload = [];

        if ($request->has("start") && $request->has("end")) {
            $resultsPayload["timespan"] = new StdClass;
            $resultsPayload["timespan"]->start = $request->start;
            $resultsPayload["timespan"]->end = $request->end;
            $resultsPayload["timespan"]->results = [];
        }

        $resultsPayload["total"] = new StdClass;
        $resultsPayload["total"]->start = $request->start;
        $resultsPayload["total"]->end = $request->end;
        $resultsPayload["total"]->results = [];

        foreach ($this->cacheTimeSpans as $key => $timespan) {
            $resultsPayload[$key] = new StdClass;
            $resultsPayload[$key]->start = $timespan["start"];
            $resultsPayload[$key]->end = $timespan["end"];
            $resultsPayload[$key]->results = [];
        }

        $className = 'Twoavy\EvaluationTool\SurveyElementTypes\EvaluationToolSurveyElementType' . ucfirst($elementType);
        if (class_exists($className)) {
            if (method_exists($className, "statsCountResult")) {
                if ($request->has("start") && $request->has("end")) {
                    foreach ($resultsSpan as $result) {
                        $resultsPayload["timespan"]->results = $className::statsCountResult($result, $resultsPayload["timespan"]->results);
                    }
                }

                foreach ($results as $result) {
                    $resultsPayload["total"]->results = $className::statsCountResult($result, $resultsPayload["total"]->results);
                }

                foreach ($results as $result) {
                    foreach ($this->cacheTimeSpans as $key => $timespan) {
                        if ($result->answered_at->between($timespan["start"], $timespan["end"])) {
                            $resultsPayload[$key]->results = $className::statsCountResult($result, $resultsPayload[$key]->results);
                        }
                    }
                }
            }
        }

        foreach ($this->cacheTimeSpans as $key => $timespan) {
            if (empty($resultsPayload[$key]->results)) {
                unset($resultsPayload[$key]);
            } else {
                if ($elementType == "textInput") {
                    $analysis = $this->getTextAnalysis($resultsPayload[$key]->results);
                    $resultsPayload[$key]->results["analysis"] = $analysis;
                }

            }
        }
        if ($elementType == "textInput") {
            $analysis = $this->getTextAnalysis($resultsPayload["total"]->results);
            $resultsPayload["total"]->results["analysis"] = $analysis;

            // timespan
            $analysis = $this->getTextAnalysis($resultsPayload["timespan"]->results);
            $resultsPayload["timespan"]->results["analysis"] = $analysis;
        }

        $payload = new StdClass;
//        $payload->total         = $results->count();
        $payload->results = $resultsPayload;
        $payload->elementType = $elementType;
        $payload->elementParams = $step->survey_element->params;

        return $this->successResponse($payload);
    }

    public function getStats(EvaluationToolSurvey $survey, EvaluationToolSurveyStatsIndexRequest $request): JsonResponse
    {
        $this->getCacheTimeSpans($survey);

        $resultQuery = EvaluationToolSurveyStatsCache::where("survey_id", $survey->id);

        // check for start date
        if ($request->has("start")) {
            $resultQuery->where("date", ">=", Carbon::createFromFormat("Y-m-d", $request->start)->startOfDay());
        }

        // check for end date
        if ($request->has("end")) {
            $resultQuery->where("date", "<=", Carbon::createFromFormat("Y-m-d", $request->end)->endOfDay());
        }

        $results = $resultQuery->get();

        return $this->showAll($results);
    }

    public function parseResults($results): stdClass
    {
        $resultsByStep = new StdClass;
        $resultsByStep->totals = new StdClass;
        $resultsByStep->totals->total = 0;

        $resultsByStep->deltas = new StdClass;

        $resultsByStep->steps = [];

        foreach ($results as $result) {
            if (!isset($resultsByStep->steps[$result->survey_step_id])) {
                $resultsByStep->steps[$result->survey_step_id] = new StdClass;
                $resultsByStep->steps[$result->survey_step_id]->stepId = $result->survey_step_id;
                $resultsByStep->steps[$result->survey_step_id]->total = 0;
                $resultsByStep->steps[$result->survey_step_id]->results = new StdClass;
                foreach ($this->timeSpans as $key => $timeSpan) {
                    $resultsByStep->steps[$result->survey_step_id]->{$key} = new StdClass;
                    $resultsByStep->steps[$result->survey_step_id]->{$key}->total = 0;
                    $resultsByStep->steps[$result->survey_step_id]->{$key}->results = new StdClass;
                }

                $resultsByStep->steps[$result->survey_step_id]->type = $result->survey_step->survey_element->survey_element_type->key;
                $resultsByStep->steps[$result->survey_step_id]->params = $result->survey_step->survey_element->params;
            }
            $elementType = $result->survey_step->survey_element->survey_element_type->key;
            $className = 'Twoavy\EvaluationTool\SurveyElementTypes\EvaluationToolSurveyElementType' . ucfirst($elementType);

            /*foreach ($this->timeSpans as $key => $timeSpan) {
            // per result
            $cacheKey = $this->survey->id . "_" . $key;
            if (Carbon::parse($result->answered_at)->between($timeSpan["start"], $timeSpan["end"])) {
            $resultsByStep->steps[$result->survey_step_id]->$key->total++;

            if (class_exists($className)) {
            if (method_exists($className, "statsCountResult")) {
            $className::statsCountResult($result, $resultsByStep->steps[$result->survey_step_id]->$key->results);
            $className::statsCountResult($result, $resultsByStep->steps[$result->survey_step_id]->results);
            }
            }

            // timespan total
            if (!isset($resultsByStep->totals->{$key})) {
            $resultsByStep->totals->{$key} = 0;
            }
            $resultsByStep->totals->{$key}++;
            }
            }*/
            $resultsByStep->steps[$result->survey_step_id]->total++;
            $resultsByStep->totals->total++;
        }

        $resultsByStep->steps = collect($resultsByStep->steps)->values();

        return $resultsByStep;
    }

    public function checkBetweenDates($date, $start, $end)
    {
        $startDate = Carbon::createFromFormat('Y-m-d', '2020-11-01');
        $endDate = Carbon::createFromFormat('Y-m-d', '2020-11-30');

        $check = Carbon::now()->between($startDate, $endDate);
    }

    public function getStatsList(EvaluationToolSurvey $survey, EvaluationToolSurveyStatsIndexRequest $request): JsonResponse
    {
        $requestValues = $request->all();
        ksort($requestValues);
        $cacheKey = md5($survey->id . json_encode($requestValues));
        $resultsByUuid = Cache::remember("stats-list-" . $cacheKey, Carbon::now()->addSeconds(30), function () use ($survey, $request) {

            $ordering = EvaluationToolHelper::sortSurveySteps($survey);
            $results = EvaluationToolSurveyStepResult::whereIn("survey_step_id",
                $survey->survey_steps
                    ->pluck("id"))
                ->orderByRaw(DB::raw("FIELD(survey_step_id, " . implode(",", $ordering->toArray()) . ") ASC"))
                ->orderBy("answered_at", "DESC");

            // check for start date
            if ($request->has("start")) {
                $results->where("answered_at", ">=", Carbon::createFromFormat("Y-m-d", $request->start)->startOfDay());
            }

            // check for end date
            if ($request->has("end")) {
                $results->where("answered_at", "<=", Carbon::createFromFormat("Y-m-d", $request->end)->endOfDay());
            }

            if ($request->has("demo") && $request->demo == true) {
                $results->where("demo", true);
            } else {
                $results->where("demo", false);
            }

            $results = $results->get();

            $resultsByUuid = new StdClass;
            foreach ($results as $result) {
                $elementType = $result->survey_step->survey_element_type->key;

                if (!isset($resultsByUuid->{$result->session_id})) {
                    $resultsByUuid->{$result->session_id} = new StdClass;
                    $resultsByUuid->{$result->session_id}->uuid = $result->session_id;
                    $resultsByUuid->{$result->session_id}->firstResultTimestamp = Carbon::now()->addYears(10);
                    $resultsByUuid->{$result->session_id}->lastResultTimestamp = Carbon::now()->subYears(10);
                    $resultsByUuid->{$result->session_id}->duration = 0;
                    $resultsByUuid->{$result->session_id}->resultCount = 0;
                    $resultsByUuid->{$result->session_id}->results = [];
                }

                if ($resultsByUuid->{$result->session_id}->firstResultTimestamp > $result->answered_at) {
                    $resultsByUuid->{$result->session_id}->firstResultTimestamp = $result->answered_at;
                }

                if ($resultsByUuid->{$result->session_id}->lastResultTimestamp < $result->answered_at) {
                    $resultsByUuid->{$result->session_id}->lastResultTimestamp = $result->answered_at;
                }

                $resultsByUuid->{$result->session_id}->duration = $resultsByUuid->{$result->session_id}->lastResultTimestamp->diffInSeconds(
                    $resultsByUuid->{$result->session_id}->firstResultTimestamp);

                $resultsByUuid->{$result->session_id}->resultCount++;

                $resultValue = new StdClass;
                $resultValue->value = $result->result_value;

                // handle voice input
                if ($elementType == "voiceInput") {
                    if ($asset = $result->result_asset) {
                        $assetUrl = Storage::disk("evaluation_tool_audio")->url($asset->filename);

                        $resultValue->value = [
                            "url" => $assetUrl,
                            "language" => $result->language->code,
                            "apiTranscription" => $asset->audio_transcription ? $asset->audio_transcription->api_transcription : null,
                            "manualTranscription" => $asset->audio_transcription ? $asset->audio_transcription->manual_transcription : null,
                        ];
                    }
                }

                if ($elementType == "textInput") {
                    $resultValue->value = [
                        "text" => $result->result_value['text'] ?? null,
                        "language" => $result->language->code,
                    ];
                }

                $resultValue->stepId = $result->survey_step_id;

                if ($elementType == "video") {
                    if (!isset($resultsByUuid->{$result->session_id}->results[$result->survey_step_id])) {
                        $resultsByUuid->{$result->session_id}->results[$result->survey_step_id] = [];
                    }

                    $preparedResult = [
                        "time" => $result->time,
                        "language" => $result->language->code,
                        "result" => $result->result_value,
                    ];

                    if ($asset = $result->result_asset) {
                        $assetUrl = Storage::disk("evaluation_tool_audio")->url($asset->filename);

                        $preparedResult["url"] = $assetUrl;
                        $preparedResult["apiTranscription"] = $asset->audio_transcription ? $asset->audio_transcription->api_transcription : null;
                        $preparedResult["manualTranscription"] = $asset->audio_transcription ? $asset->audio_transcription->manual_transcription : null;
                    }

                    $resultsByUuid->{$result->session_id}->results[$result->survey_step_id][] = $preparedResult;

                } else {
                    $resultsByUuid->{$result->session_id}->results[$result->survey_step_id] = $resultValue;
                }
            }

            return $resultsByUuid;
        });

        // collect and flatten results (remove keys)
        $resultsByUuid = collect($resultsByUuid)->map(function ($resultSetByUuid) {
            $resultSetByUuid->results = collect($resultSetByUuid->results)->flatten(1);
            return $resultSetByUuid;
        });

        return $this->showAll($resultsByUuid);
    }

    public function getStatsListScheme(EvaluationToolSurvey $survey): JsonResponse
    {
        $steps = $survey->survey_steps;

        // get first step and set as starting element
        $firstStep = $steps->where("is_first_step")->first();
        $stepFlow = new Collection();
        $stepFlow->add($this->stepForFlow($firstStep));

        $stepFlow = $this->getNextSteps($firstStep, $stepFlow);

        return $this->successResponse($stepFlow);
    }

    public function getNextSteps(EvaluationToolSurveyStep $step, Collection $stepFlow): Collection
    {
        if ($step->next_step_id) {
            $subFlow = new Collection();
            $nextStep = EvaluationToolSurveyStep::find($step->next_step_id);
            $subFlow->add($this->stepForFlow($nextStep));
            $this->getNextSteps($nextStep, $subFlow);
            $stepFlow->add($subFlow);
        }
        if ($step->result_based_next_steps && !empty($step->result_based_next_steps)) {
            $elementType = $step->survey_element->survey_element_type->key;

            if ($elementType == "binary") {
                $subFlow = new Collection();
                if (isset($step->result_based_next_steps->trueNextStep->stepId)) {
                    $nextStep = EvaluationToolSurveyStep::find($step->result_based_next_steps->trueNextStep->stepId);
                    $subFlow->add($this->stepForFlow($nextStep));
                    $this->getNextSteps($nextStep, $subFlow);
                }

                if (isset($step->result_based_next_steps->falseNextStep->stepId)) {
                    $nextStep = EvaluationToolSurveyStep::find($step->result_based_next_steps->falseNextStep->stepId);
                    $subFlow->add($this->stepForFlow($nextStep));
                    $this->getNextSteps($nextStep, $subFlow);
                }
                $stepFlow->add($subFlow);
            }
            if ($elementType == "starRating") {
                $subFlow = new Collection();
                foreach (collect($step->result_based_next_steps)->pluck("stepId") as $step) {
                    $nextStep = EvaluationToolSurveyStep::find($step);
                    $subFlow->add($this->stepForFlow($nextStep));
                    $this->getNextSteps($nextStep, $subFlow);
                }
                $stepFlow->add($subFlow);
            }
            if ($elementType == "multipleChoice") {
                $subFlow = new Collection();
                foreach ($step->result_based_next_steps as $step) {
                    $nextStep = EvaluationToolSurveyStep::find($step->stepId);
                    $subFlow->add($this->stepForFlow($nextStep));
                    $this->getNextSteps($nextStep, $subFlow);
                }
                $stepFlow->add($subFlow);
            }
        }
        return $stepFlow;
    }

    public function stepForFlow($step): stdClass
    {
        $stepForFlow = new StdClass;
        $stepForFlow->id = $step->id;
        $stepForFlow->elementType = $step->survey_element_type->key;

        return $stepForFlow;
    }
}
