<?php

namespace Twoavy\EvaluationTool\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use stdClass;
use Twoavy\EvaluationTool\Http\Requests\EvaluationToolSurveyStatsIndexRequest;
use Twoavy\EvaluationTool\Models\EvaluationToolSurvey;
use Twoavy\EvaluationTool\Models\EvaluationToolSurveyStep;
use Twoavy\EvaluationTool\Models\EvaluationToolSurveyStepResult;
use Twoavy\EvaluationTool\Traits\EvaluationToolResponse;

class EvaluationToolSurveyStatsController extends Controller
{
    use EvaluationToolResponse;

    public function __construct()
    {
        $this->middleware("auth:api");
    }

    public function getStats(EvaluationToolSurvey $survey, EvaluationToolSurveyStatsIndexRequest $request): JsonResponse
    {
        $results = EvaluationToolSurveyStepResult::whereIn("survey_step_id", $survey->survey_steps->pluck("id"))->orderBy('session_id', 'ASC');

        // check for step
        if ($request->has("step")) {
            if (!$step = EvaluationToolSurveyStep::find($request->step)) {
                return $this->errorResponse("step not found", 409);
            }
            if ($step->survey_id != $survey->id) {
                return $this->errorResponse("survey does not match step", 409);
            }

            $results = EvaluationToolSurveyStepResult::where("survey_step_id", $request->step)->orderBy('session_id', 'ASC');
        }

        // check for start date
        if ($request->has("start")) {
            $results->where("answered_at", ">=", $request->start);
        }

        // check for end date
        if ($request->has("start")) {
            $results->where("answered_at", "<=", $request->end);
        }

        if ($request->has("demo") && $request->demo == true) {
            $results->where("demo", true);
        } else {
            $results->where("demo", false);
        }

        $results = $results->get();

        $results = $this->parseResults($results);

        return $this->successResponse($results);

    }

    public function parseResults($results): stdClass
    {
        $resultsByStep        = new StdClass;
        $resultsByStep->total = 0;
        $resultsByStep->steps = [];

        $timeSpans = array(
            array("yesterday", Carbon::yesterday()->startOfDay(), Carbon::yesterday()->endOfDay()),
            array("currentWeek", Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()),
            array("lastWeek", Carbon::now()->subWeek(1)->startOfWeek(), Carbon::now()->subWeek(1)->endOfWeek()),
            array("currentMonth", Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()),
            array("lastMonth", Carbon::now()->subMonth(1)->startOfMonth(), Carbon::now()->subMonth(1)->endOfMonth()),
            array("currentYear", Carbon::now()->startOfYear(), Carbon::now()->endOfYear()),
            array("lastYear", Carbon::now()->subYear(1)->startOfYear(), Carbon::now()->subYear(1)->endOfYear()),
        );

        foreach ($results as $result) {
            if (!isset($resultsByStep->steps[$result->survey_step_id])) {
                $resultsByStep->steps[$result->survey_step_id]                        = new StdClass;
                $resultsByStep->steps[$result->survey_step_id]->stepId                = $result->survey_step_id;
                $resultsByStep->steps[$result->survey_step_id]->total                 = 0;
                $resultsByStep->steps[$result->survey_step_id]->today                 = new StdClass;
                $resultsByStep->steps[$result->survey_step_id]->today->total          = 0;
                $resultsByStep->steps[$result->survey_step_id]->today->results        = new StdClass;
                $resultsByStep->steps[$result->survey_step_id]->yesterday             = new StdClass;
                $resultsByStep->steps[$result->survey_step_id]->yesterday->total      = 0;
                $resultsByStep->steps[$result->survey_step_id]->yesterday->results    = new StdClass;
                $resultsByStep->steps[$result->survey_step_id]->currentWeek           = new StdClass;
                $resultsByStep->steps[$result->survey_step_id]->currentWeek->total    = 0;
                $resultsByStep->steps[$result->survey_step_id]->currentWeek->results  = new StdClass;
                $resultsByStep->steps[$result->survey_step_id]->lastWeek              = new StdClass;
                $resultsByStep->steps[$result->survey_step_id]->lastWeek->total       = 0;
                $resultsByStep->steps[$result->survey_step_id]->lastWeek->results     = new StdClass;
                $resultsByStep->steps[$result->survey_step_id]->currentMonth          = new StdClass;
                $resultsByStep->steps[$result->survey_step_id]->currentMonth->total   = 0;
                $resultsByStep->steps[$result->survey_step_id]->currentMonth->results = new StdClass;
                $resultsByStep->steps[$result->survey_step_id]->lastMonth             = new StdClass;
                $resultsByStep->steps[$result->survey_step_id]->lastMonth->total      = 0;
                $resultsByStep->steps[$result->survey_step_id]->lastMonth->results    = new StdClass;
                $resultsByStep->steps[$result->survey_step_id]->currentYear           = new StdClass;
                $resultsByStep->steps[$result->survey_step_id]->currentYear->total    = 0;
                $resultsByStep->steps[$result->survey_step_id]->currentYear->results  = new StdClass;
                $resultsByStep->steps[$result->survey_step_id]->lastYear              = new StdClass;
                $resultsByStep->steps[$result->survey_step_id]->lastYear->total       = 0;
                $resultsByStep->steps[$result->survey_step_id]->lastYear->results     = new StdClass;
                $resultsByStep->steps[$result->survey_step_id]->type                  = $result->survey_step->survey_element->survey_element_type->key;
                $resultsByStep->steps[$result->survey_step_id]->params                = $result->survey_step->survey_element->params;
            }
            $elementType = $result->survey_step->survey_element->survey_element_type->key;
            $className   = 'Twoavy\EvaluationTool\SurveyElementTypes\EvaluationToolSurveyElementType' . ucfirst($elementType);

            foreach ($timeSpans as $timeSpan) {
                if (Carbon::parse($result->answered_at)->between($timeSpan[1], $timeSpan[2])) {
                    $key = $timeSpan[0];
                    $resultsByStep->steps[$result->survey_step_id]->$key->total++;

                    if (class_exists($className)) {
                        if (method_exists($className, "statsCountResult")) {
                            $className::statsCountResult($result, $resultsByStep->steps[$result->survey_step_id]->$key->results);
                        }
                    }
                }
            }
            $resultsByStep->steps[$result->survey_step_id]->total++;
            $resultsByStep->total++;
        }

        $resultsByStep->steps = collect($resultsByStep->steps)->values();

        return $resultsByStep;
    }

    public function checkBetweenDates($date, $start, $end)
    {
        $startDate = Carbon::createFromFormat('Y-m-d', '2020-11-01');
        $endDate   = Carbon::createFromFormat('Y-m-d', '2020-11-30');

        $check = Carbon::now()->between($startDate, $endDate);
    }

    public function getStatsList(EvaluationToolSurvey $survey, EvaluationToolSurveyStatsIndexRequest $request): JsonResponse
    {
        $results = EvaluationToolSurveyStepResult::whereIn("survey_step_id", $survey->survey_steps->pluck("id"))->orderBy('session_id', 'ASC');

        // check for start date
        if ($request->has("start")) {
            $results->where("answered_at", ">=", $request->start);
        }

        // check for end date
        if ($request->has("start")) {
            $results->where("answered_at", "<=", $request->end);
        }

        if ($request->has("demo") && $request->demo == true) {
            $results->where("demo", true);
        } else {
            $results->where("demo", false);
        }

        $results = $results->get();

        $resultsByUuid = new StdClass;
        foreach ($results as $result) {
            if (!isset($resultsByUuid->{$result->session_id})) {
                $resultsByUuid->{$result->session_id}                       = new StdClass;
                $resultsByUuid->{$result->session_id}->uuid                 = $result->session_id;
                $resultsByUuid->{$result->session_id}->firstResultTimestamp = Carbon::now()->addYears(10);
                $resultsByUuid->{$result->session_id}->lastResultTimestamp  = Carbon::now()->subYears(10);
                $resultsByUuid->{$result->session_id}->duration             = 0;
                $resultsByUuid->{$result->session_id}->resultCount          = 0;
                $resultsByUuid->{$result->session_id}->results              = [];
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

            $resultValue         = new StdClass;
            $resultValue->value  = $result->result_value;
            $resultValue->stepId = $result->survey_step_id;

            $resultsByUuid->{$result->session_id}->results[] = $resultValue;
        }

        return $this->showAll(collect($resultsByUuid));
    }

    public function getStatsListScheme(EvaluationToolSurvey $survey): JsonResponse
    {
        $steps = $survey->survey_steps;

        // get first step and set as starting element
        $firstStep = $steps->where("is_first_step")->first();
        $stepFlow  = new Collection();
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
        $stepForFlow              = new StdClass;
        $stepForFlow->id          = $step->id;
        $stepForFlow->elementType = $step->survey_element_type->key;

        return $stepForFlow;
    }
}
