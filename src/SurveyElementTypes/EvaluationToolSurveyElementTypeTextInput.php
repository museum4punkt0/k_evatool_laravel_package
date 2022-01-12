<?php

namespace Twoavy\EvaluationTool\SurveyElementTypes;

use Illuminate\Http\Request;
use StdClass;
use Twoavy\EvaluationTool\Models\EvaluationToolSurveyElement;
use Twoavy\EvaluationTool\Models\EvaluationToolSurveyStepResult;
use Faker\Factory;
use Twoavy\EvaluationTool\Models\EvaluationToolSurveyLanguage;

class EvaluationToolSurveyElementTypeTextInput extends EvaluationToolSurveyElementTypeBase
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
                "en" => "Question"
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

        return [
            "result_value.text" => "required|min:1|max:500"
        ];
    }

    public static function prepareResultRequest(): bool
    {
        return true;
    }

    /**
     * @return array
     */
    public static function rules(): array
    {
        return [
            'params.question'   => ['required', 'array', 'min:1'],
            'params.question.*' => self::QUESTION_RULES,
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

    public static function statsCountResult($result, $results)
    {
        $language = EvaluationToolSurveyLanguage::find($result->result_language_id);
        if (!isset($results["texts"])) {
            $results["texts"] = [];
        }
        if (!isset($results["texts"][$language->code])) {
            $results["texts"][$language->code] = [];
        }
        $text                                = $result->result_value["text"];
        $results["texts"][$language->code][] = $text;

        return $results;
    }

    public static function seedResult($surveyStep, $uuid, $languageId, $timestamp)
    {
        $surveyResult                     = new EvaluationToolSurveyStepResult();
        $surveyResult->session_id         = $uuid;
        $surveyResult->demo               = true;
        $surveyResult->survey_step_id     = $surveyStep->id;
        $surveyResult->result_language_id = $languageId;
        $surveyResult->answered_at        = $timestamp;
        $surveyResult->params             = $surveyStep->survey_element->params;
        $language                         = EvaluationToolSurveyLanguage::find($languageId);

        $faker                      = Factory::create($language->sub_code);
        $resultValue                = new StdClass;
        $text                       = $faker->realText(400);
        $resultValue->text          = $text;
        $surveyResult->result_value = $resultValue;

        $surveyResult->save();

        return $surveyResult;
    }

    public static function getExportData(EvaluationToolSurveyElement $element, EvaluationToolSurveyLanguage $language): array
    {
        $numberOfOptions = 1;
        $exportData      = [];

        $exportData["elements"]   = [];
        $exportData["elements"][] = [
            "value" => $element->survey_element_type->key,
            "span"  => $numberOfOptions,
        ];

        $exportData["question"]   = [];
        $exportData["question"][] = [
            "value" => $element->params->question->{$language->code},
            "span"  => $numberOfOptions,
        ];

        $exportData["options"]   = [];
        $exportData["options"][] = [
            "value" => "textinput",
            "span"  => $numberOfOptions,
        ];

        return $exportData;
    }
}
