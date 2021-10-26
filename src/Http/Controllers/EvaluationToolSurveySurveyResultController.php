<?php

namespace Twoavy\EvaluationTool\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Twoavy\EvaluationTool\Http\Requests\EvaluationToolSurveyStepStoreRequest;
use Twoavy\EvaluationTool\Models\EvaluationToolAsset;
use Twoavy\EvaluationTool\Models\EvaluationToolSurvey;
use Twoavy\EvaluationTool\Models\EvaluationToolSurveyElement;
use Twoavy\EvaluationTool\Models\EvaluationToolSurveyStep;
use Twoavy\EvaluationTool\Traits\EvaluationToolResponse;

class EvaluationToolSurveySurveyResultController extends Controller
{
    use EvaluationToolResponse;

    public function __construct()
    {
        $this->middleware("auth:api");
    }

    /**
     * @param EvaluationToolSurvey $survey
     * @return JsonResponse
     */
    public function index(EvaluationToolSurvey $survey): JsonResponse
    {
        $surveySteps = $survey->survey_steps;
        return $this->showAll($surveySteps);
    }

    /**
     * @param EvaluationToolSurvey $survey
     * @param EvaluationToolSurveyStep $result
     * @return JsonResponse
     */
    public function show(EvaluationToolSurvey $survey, EvaluationToolSurveyStep $result): JsonResponse
    {

        if ($result->survey_id != $survey->id) {
            return $this->errorResponse("survey step does not belong to survey", 409);
        }

        $results = $this->prepareResults($result->survey_step_results, $result->survey_element);

        return $this->successResponse(["data" => $results], 200);
    }

    /**
     * @param $stepResults
     * @param EvaluationToolSurveyElement $surveyElement
     * @return void
     */
    private function prepareResults($stepResults, EvaluationToolSurveyElement $surveyElement)
    {
        $functionName = 'prepare' . ucfirst($surveyElement->survey_element_type->key) . "Results";
        if (method_exists($this, $functionName)) {
            return $this->{$functionName}($stepResults, $surveyElement);
        }
    }

    private function prepareStarRatingResults($results, $element): array
    {
        $resultsPrepared = [];

        foreach ($results as $result) {
            $key = "value_" . $result->result_value["rating"];
            if (!isset($resultsPrepared[$key])) {
                $resultsPrepared[$key] = [
                    "value" => (int)$result->result_value["rating"],
                    "count" => 0
                ];
            }
            $resultsPrepared[$key]["count"]++;
        }

        return $resultsPrepared;
    }

    private function prepareYayNayResults($results, $element): array
    {
        $resultsPrepared = [];

        foreach ($results as $result) {
            foreach ($result->result_value["images"] as $image) {
                $key = "image_" . $image["asset"];
                if (!isset($resultsPrepared[$key])) {
                    $resultsPrepared[$key] = [
                        "results" => [
                            $element->params->trueValue  => 0,
                            $element->params->falseValue => 0,
                        ],
                        "asset"   => EvaluationToolAsset::find($image["asset"]),
                    ];
                }
                if ($image["value"] == $element->params->trueValue) {
                    $resultsPrepared[$key]["results"][$element->params->trueValue]++;
                }
                if ($image["value"] == $element->params->falseValue) {
                    $resultsPrepared[$key]["results"][$element->params->falseValue]++;
                }

            }
        }

        return $resultsPrepared;
    }

}
