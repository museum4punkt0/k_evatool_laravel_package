<?php

namespace Twoavy\EvaluationTool\SurveyElementTypes;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use StdClass;
use Twoavy\EvaluationTool\Models\EvaluationToolAsset;
use Twoavy\EvaluationTool\Models\EvaluationToolSurveyElement;
use Twoavy\EvaluationTool\Models\EvaluationToolSurveyStep;
use Twoavy\EvaluationTool\Models\EvaluationToolSurveyStepResult;
use Twoavy\EvaluationTool\Rules\IsMediaType;

class EvaluationToolSurveyElementTypeVideo extends EvaluationToolSurveyElementTypeBase
{

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @return array
     */
    public function sampleParams(): array
    {
        return [
            "videoAssetId" => EvaluationToolAsset::where("mime", "LIKE", 'video/%')->first()->id,
        ];
    }

    public static function typeParams(): StdClass
    {
        return new StdClass();
    }

    public static function prepareRequest(Request $request)
    {

    }

    public static function prepareResultRules(EvaluationToolSurveyElement $surveyElement): array
    {
        return [];
    }

    /**
     * @return array
     */
    public static function rules(): array
    {
        return [
            'params.videoAssetId' => [
                "required",
                "exists:evaluation_tool_assets,id",
                new IsMediaType("video")
            ]
        ];
    }

    /**
     * @param Request $request
     * @param EvaluationToolSurveyElement $surveyElement
     * @return bool
     */
    public static function validateResultBasedNextSteps(Request $request, EvaluationToolSurveyElement $surveyElement): bool
    {
        return true;
    }

    public static function validateTimeBasedSteps(Request $request, EvaluationToolSurveyElement $surveyElement): bool
    {
        $surveyId = Route::current()->parameters["survey"]->id;

        if ($request->has("time_based_steps") && is_array($request->time_based_steps)) {
            foreach ($request->time_based_steps as $timeBasedStep) {
                if (!$step = EvaluationToolSurveyStep::find($timeBasedStep["stepId"])) {
                    abort(409, "step does not exist");
                }
                if ($step->survey_id != $surveyId) {
                    abort(409, "survey id does not match time based step survey id");
                }
            }
        }
        return true;
    }

    public static function prepareResultRequest(): bool
    {
        return true;
    }

    public static function seedResult($surveyStep, $uuid, $languageId, $timestamp)
    {
        $videoQuestionsCounter = rand(1, 5);
        $asset                 = EvaluationToolAsset::find($surveyStep->survey_element->params->videoAssetId);

//        var_dump($asset->meta->playtime_seconds);
//        var_dump($videoQuestionsCounter);

        $timeOffset = $asset->meta->playtime_seconds / $videoQuestionsCounter;

        for ($i = 1; $i <= $videoQuestionsCounter; $i++) {

            $surveyResult                     = new EvaluationToolSurveyStepResult();
            $surveyResult->session_id         = $uuid;
            $surveyResult->demo               = true;
            $surveyResult->survey_step_id     = $surveyStep->id;
            $surveyResult->result_language_id = $languageId;
            $surveyResult->answered_at        = $timestamp;
            $surveyResult->params             = $surveyStep->survey_element->params;
            $surveyResult->time               = "00:00:" . intval($timeOffset * $i) . ":00";

            $videoComment = $i . ' This is a great comment I have for this video.';

            $resultValue                = new StdClass;
            $resultValue->text          = $videoComment;
            $surveyResult->result_value = $resultValue;
            $surveyResult->save();
        }
        return $surveyResult;
    }

    public static function statsCountResult($result, $results): void
    {
//        $value = $result->result_value;
//        echo gettype($results);
        if (!isset($results["read"])) {
            $results["read"] = 0;
        }
//        if ($value) {
        $results["read"]++;
//        }
    }

}
