<?php

namespace Twoavy\EvaluationTool\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;
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
        $results = EvaluationToolSurveyStepResult::whereIn("survey_step_id", $survey->survey_steps->pluck("id"));

        // check for step
        if ($request->has("step")) {
            if (!$step = EvaluationToolSurveyStep::find($request->step)) {
                return $this->errorResponse("step not found", 409);
            }
            if ($step->survey_id != $survey->id) {
                return $this->errorResponse("survey does not match step", 409);
            }

            $results = EvaluationToolSurveyStepResult::where("survey_step_id", $request->step);
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
        $resultsByStep = new StdClass;
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
                $resultsByStep->steps[$result->survey_step_id] = new StdClass;
                $resultsByStep->steps[$result->survey_step_id]->stepId = $result->survey_step_id;
                $resultsByStep->steps[$result->survey_step_id]->total = 0;
                $resultsByStep->steps[$result->survey_step_id]->today = new StdClass;
                $resultsByStep->steps[$result->survey_step_id]->today->total = 0;
                $resultsByStep->steps[$result->survey_step_id]->today->results = new StdClass;
                $resultsByStep->steps[$result->survey_step_id]->yesterday = new StdClass;
                $resultsByStep->steps[$result->survey_step_id]->yesterday->total = 0;
                $resultsByStep->steps[$result->survey_step_id]->yesterday->results = new StdClass;
                $resultsByStep->steps[$result->survey_step_id]->currentWeek = new StdClass;
                $resultsByStep->steps[$result->survey_step_id]->currentWeek->total = 0;
                $resultsByStep->steps[$result->survey_step_id]->currentWeek->results = new StdClass;
                $resultsByStep->steps[$result->survey_step_id]->lastWeek = new StdClass;
                $resultsByStep->steps[$result->survey_step_id]->lastWeek->total = 0;
                $resultsByStep->steps[$result->survey_step_id]->lastWeek->results = new StdClass;
                $resultsByStep->steps[$result->survey_step_id]->currentMonth = new StdClass;
                $resultsByStep->steps[$result->survey_step_id]->currentMonth->total = 0;
                $resultsByStep->steps[$result->survey_step_id]->currentMonth->results = new StdClass;
                $resultsByStep->steps[$result->survey_step_id]->lastMonth = new StdClass;
                $resultsByStep->steps[$result->survey_step_id]->lastMonth->total = 0;
                $resultsByStep->steps[$result->survey_step_id]->lastMonth->results = new StdClass;
                $resultsByStep->steps[$result->survey_step_id]->currentYear = new StdClass;
                $resultsByStep->steps[$result->survey_step_id]->currentYear->total = 0;
                $resultsByStep->steps[$result->survey_step_id]->currentYear->results = new StdClass;
                $resultsByStep->steps[$result->survey_step_id]->lastYear = new StdClass;
                $resultsByStep->steps[$result->survey_step_id]->lastYear->total = 0;
                $resultsByStep->steps[$result->survey_step_id]->lastYear->results = new StdClass;
                $resultsByStep->steps[$result->survey_step_id]->type = $result->survey_step->survey_element->survey_element_type->key;
                $resultsByStep->steps[$result->survey_step_id]->params = $result->survey_step->survey_element->params;
            }
            $elementType = $result->survey_step->survey_element->survey_element_type->key;
            $className = 'Twoavy\EvaluationTool\SurveyElementTypes\EvaluationToolSurveyElementType' . ucfirst($elementType);

            foreach ($timeSpans as $timeSpan) {
                if (Carbon::parse($result->answered_at)->between($timeSpan[1], $timeSpan[2])) {
                    $key = $timeSpan[0];
                    $resultsByStep->steps[$result->survey_step_id]->$key->total++;

                    if (class_exists($className)) {
                        if (method_exists($className, "getResult")) {
                            $className::getResult($result, $resultsByStep->steps[$result->survey_step_id]->$key->results);
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
        $endDate = Carbon::createFromFormat('Y-m-d', '2020-11-30');

        $check = Carbon::now()->between($startDate, $endDate);
    }

}
