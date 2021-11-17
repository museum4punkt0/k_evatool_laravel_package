<?php

namespace Twoavy\EvaluationTool\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
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
        $resultsByStep        = new StdClass;
        $resultsByStep->total = 0;
        $resultsByStep->steps = [];

        foreach ($results as $result) {
            if (!isset($resultsByStep->steps[$result->survey_step_id])) {
                $resultsByStep->steps[$result->survey_step_id]                     = new StdClass;
                $resultsByStep->steps[$result->survey_step_id]->stepId             = $result->survey_step_id;
                $resultsByStep->steps[$result->survey_step_id]->total              = 0;
                $resultsByStep->steps[$result->survey_step_id]->today              = new StdClass;
                $resultsByStep->steps[$result->survey_step_id]->today->total       = 0;
                $resultsByStep->steps[$result->survey_step_id]->yesterday          = new StdClass;
                $resultsByStep->steps[$result->survey_step_id]->yesterday->total   = 0;
                $resultsByStep->steps[$result->survey_step_id]->currentWeek        = new StdClass;
                $resultsByStep->steps[$result->survey_step_id]->currentWeek->total = 0;
                $resultsByStep->steps[$result->survey_step_id]->lastWeek           = new StdClass;
                $resultsByStep->steps[$result->survey_step_id]->lastWeek->total    = 0;
                $resultsByStep->steps[$result->survey_step_id]->type = $result->survey_step->survey_element->survey_element_type->key;
                $resultsByStep->steps[$result->survey_step_id]->params = $result->survey_step->survey_element->params;
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

}
