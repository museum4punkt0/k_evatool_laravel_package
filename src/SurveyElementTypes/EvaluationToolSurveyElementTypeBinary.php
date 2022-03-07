<?php

namespace Twoavy\EvaluationTool\SurveyElementTypes;

use Illuminate\Http\Request;
use StdClass;
use Twoavy\EvaluationTool\Helpers\EvaluationToolHelper;
use Twoavy\EvaluationTool\Models\EvaluationToolSurvey;
use Twoavy\EvaluationTool\Models\EvaluationToolSurveyElement;
use Twoavy\EvaluationTool\Models\EvaluationToolSurveyLanguage;
use Twoavy\EvaluationTool\Models\EvaluationToolSurveyStep;
use Twoavy\EvaluationTool\Models\EvaluationToolSurveyStepResult;
use Twoavy\EvaluationTool\Rules\SnakeCase;

class EvaluationToolSurveyElementTypeBinary extends EvaluationToolSurveyElementTypeBase
{

    const PARAMS_KEYS = ["question", "trueLabel", "falseLabel"];

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @return array
     */
    public function sampleParams(): array
    {

        $question                               = [];
        $question[$this->primaryLanguage->code] = $this->faker->words($this->faker->numberBetween(3, 20), true);
        foreach ($this->secondaryLanguages as $secondaryLanguage) {
            if ($this->faker->boolean(60)) {
                $question[$secondaryLanguage->code] = $this->faker->words($this->faker->numberBetween(3, 20), true);
            }
        }

        return [
            "question"   => $question,
            "trueValue"  => "accepted",
            "falseValue" => "declined",
            "trueLabel"  => ["de" => "ja", "en" => "yes"],
            "falseLabel" => ["de" => "nein", "en" => "no"],
        ];
    }

    public static function typeParams(): StdClass
    {
        return new StdClass();
    }

    public static function prepareRequest(Request $request)
    {
        $languageKeys = [];
        if ($request->has('params.question')) {
            if (is_array($request->params['question'])) {
                foreach ($request->params['question'] as $key => $value) {
                    $languageKeys["question_" . $key] = $key;
                }
            }
        }
        $request->request->add(['languageKeys' => $languageKeys]);
    }

    public static function prepareResultRules(EvaluationToolSurveyElement $surveyElement): array
    {
        $trueValue  = $surveyElement->params->trueValue;
        $falseValue = $surveyElement->params->falseValue;
        return [
            "result_value.value" => ['required', 'in:' . $trueValue . ',' . $falseValue],
        ];
    }

    /**
     * @return array
     */
    public static function rules(): array
    {
        return [
            'params.question'   => 'required|array',
            'params.question.*' => self::QUESTION_RULES,
            'languageKeys.*'    => 'required|exists:evaluation_tool_survey_languages,code',
            'params.trueValue'  => ["required", "min:1", "max:20", new SnakeCase()],
            'params.falseValue' => ["required", "min:1", "max:20", new SnakeCase()],
        ];
    }

    public static function prepareResultRequest(): bool
    {
        return true;
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

    public static function getResultBasedNextStep(EvaluationToolSurveyStep $surveyStep)
    {
        if (isset($surveyStep->resultByUuid["value"])) {
            $value = $surveyStep->resultByUuid["value"];
            if ($value == $surveyStep->survey_element->params->trueValue) {
                return $surveyStep->result_based_next_steps->trueNextStep->stepId;
            }
            if ($value == $surveyStep->survey_element->params->falseValue) {
                return $surveyStep->result_based_next_steps->falseNextStep->stepId;
            }
        }
        return $surveyStep->next_step_id;
    }

    public static function validateSurveyBasedLanguages(EvaluationToolSurveyElement $element): array
    {
        $keysToCheck = self::PARAMS_KEYS;

        return EvaluationToolHelper::checkMissingLanguages($element, $keysToCheck);
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
        $binaryValues                     = array($surveyResult->params['trueValue'], $surveyResult->params['falseValue']);

        $resultValue                = new StdClass;
        $resultValue->value         = $binaryValues[array_rand($binaryValues, 1)];
        $surveyResult->result_value = $resultValue;

        $surveyResult->save();

        return $surveyResult;
    }

    public static function statsCountResult($result, $results)
    {
        $value = $result->result_value["value"];

        // get element and meaning values
        $element    = $result->survey_step->survey_element;
        $trueValue  = $element->params->trueValue;
        $falseValue = $element->params->falseValue;

        if (!isset($results[$trueValue])) {
            $results[$trueValue] = 0;
        }

        if (!isset($results[$falseValue])) {
            $results[$falseValue] = 0;
        }

        if (in_array($value, array($trueValue, $falseValue))) {
            $results[$value]++;
        }
        return $results;
    }

    public static function checkCompleteLanguages($request)
    {
        EvaluationToolHelper::checkCompleteLanguages($request, self::PARAMS_KEYS);
    }

    public static function getExportDataHeaders(EvaluationToolSurveyStep $step, EvaluationToolSurveyLanguage $language): array
    {
        $numberOfOptions = 1;
        $exportData      = [];

        $exportData["elements"]   = [];
        $exportData["elements"][] = [
            "value" => $step->survey_element->survey_element_type->key,
            "span"  => $numberOfOptions,
        ];

        $exportData["question"]   = [];
        $exportData["question"][] = [
            "value" => $step->survey_element->params->question->{$language->code},
            "span"  => $numberOfOptions,
        ];

        $exportData["options"]   = [];
        $exportData["options"][] = [
            "value" => $step->survey_element->params->trueValue . "/" . $step->survey_element->params->falseValue,
            "span"  => 1
        ];
        /*$exportData["options"][] = [
            "value" => $step->survey_element->params->falseValue,
            "span"  => 1
        ];*/


        return $exportData;
    }

    public static function getExportDataResult(EvaluationToolSurveyElement $element, EvaluationToolSurveyLanguage $language, $result, $position): array
    {
        return [
            ["value" => $result->result_value["value"], "position" => $position]
        ];
    }

    public static function isResultBasedMatch($result, $step): bool
    {
        $value = $step->resultByUuid["value"];

        if ($value == $step->survey_element->params->trueValue) {
            return false;
        }

        if ($value == $step->survey_element->params->falseValue) {
            return false;
        }

        return true;
    }
}
