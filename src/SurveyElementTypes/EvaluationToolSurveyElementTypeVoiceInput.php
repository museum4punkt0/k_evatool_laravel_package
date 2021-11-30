<?php

namespace Twoavy\EvaluationTool\SurveyElementTypes;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use StdClass;
use Twoavy\EvaluationTool\Http\Controllers\EvaluationToolSurveyStepResultAssetController;
use Twoavy\EvaluationTool\Http\Controllers\EvaluationToolSurveySurveyRunController;
use Twoavy\EvaluationTool\Models\EvaluationToolSurveyElement;
use Twoavy\EvaluationTool\Models\EvaluationToolSurveyStepResult;

class EvaluationToolSurveyElementTypeVoiceInput extends EvaluationToolSurveyElementTypeBase
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
            "question" => [
                "de" => "Frage",
                "en" => "Question",
                "fr" => "Question",
            ]
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
            'params.question' => ['required', 'array', 'min:1'],
        ];
    }

    /**
     * @param Request $request
     * @param EvaluationToolSurveyElement $surveyElement
     * @return bool
     */
    public static function validateResultBasedNextSteps(Request $request, EvaluationToolSurveyElement $surveyElement): bool
    {
        if ($request->has("result_based_next_steps")) {
            $resultBasedNextSteps = $request->result_based_next_steps;
            if (is_array($resultBasedNextSteps) && !empty($resultBasedNextSteps)) {
                abort(409, "survey element type '" . $surveyElement->survey_element_type->key . "' does not support result based next steps");
            }
        }
        return true;
    }

    public static function prepareResultRequest(): bool
    {
        return true;
    }

    public static function seedResult($surveyStep, $uuid, $languageId, $timestamp): EvaluationToolSurveyStepResult
    {
        $surveyResult                     = new EvaluationToolSurveyStepResult();
        $surveyResult->session_id         = $uuid;
        $surveyResult->demo               = true;
        $surveyResult->survey_step_id     = $surveyStep->id;
        $surveyResult->result_language_id = $languageId;
        $surveyResult->answered_at        = $timestamp;
        $surveyResult->params             = $surveyStep->survey_element->params;

        $resultValue                = new StdClass;
        $surveyResult->result_value = $resultValue;

        $surveyResult->save();

        $audioFile = collect(Storage::disk("evaluation_tool_demo_result_assets")->files())->random(1)[0];
        $audioData = base64_encode(Storage::disk("evaluation_tool_demo_result_assets")->get($audioFile));
        (new EvaluationToolSurveyStepResultAssetController)->createStepResultAsset($audioData, $surveyResult->id, Str::uuid());


        return $surveyResult;
    }

    public static function statsCountResult($result, $results)
    {
        $results["todo"] = "EvaluationToolSurveyElementTypeVoiceInput::statsCountResult";

        return $results;
    }
}
