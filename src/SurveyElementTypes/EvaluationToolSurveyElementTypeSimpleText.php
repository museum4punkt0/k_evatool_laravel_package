<?php

namespace Twoavy\EvaluationTool\SurveyElementTypes;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use StdClass;
use Twoavy\EvaluationTool\Helpers\EvaluationToolHelper;
use Twoavy\EvaluationTool\Models\EvaluationToolSurveyElement;
use Twoavy\EvaluationTool\Models\EvaluationToolSurveyStepResult;

class EvaluationToolSurveyElementTypeSimpleText extends EvaluationToolSurveyElementTypeBase
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
        $text                               = [];
        $text[$this->primaryLanguage->code] = $this->faker->words($this->faker->numberBetween(3, 50), true);
        foreach ($this->secondaryLanguages as $secondaryLanguage) {
            if ($this->faker->boolean(30)) {
                $text[$secondaryLanguage->code] = $this->faker->words($this->faker->numberBetween(3, 50), true);
            }
        }

        return [
            "text" => $text,
        ];
    }

    public static function typeParams(): StdClass
    {
        return new StdClass();
    }

    public static function prepareRequest(Request $request)
    {
        $languageKeys = [];
        if ($request->has('params.text')) {
            if (is_array($request->params['text'])) {
                foreach ($request->params['text'] as $key => $value) {
                    $languageKeys[] = $key;
                }
            }
        }
        $request->request->add(['languageKeys' => $languageKeys]);
    }

    /**
     * @param EvaluationToolSurveyElement $surveyElement
     * @return array
     */
    public static function prepareResultRules(EvaluationToolSurveyElement $surveyElement): array
    {
        return [
            "result_value.read" => [
                'required',
                'boolean',
                Rule::in([true]),
            ],
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
        $maxCount = 10;
        return [
            'params.text'    => ['required', 'array', 'min:1'],
            'params.text.*'  => ['min:1', "max:500"],
            'languageKeys.*' => ['required', 'exists:evaluation_tool_survey_languages,code'],
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

    public static function validateSurveyBasedLanguages(EvaluationToolSurveyElement $element): array
    {
        $keysToCheck = ["text"];

        return EvaluationToolHelper::checkMissingLanguages($element, $keysToCheck);
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

        $resultValue                = new StdClass;
        $resultValue->read          = true;
        $surveyResult->result_value = $resultValue;

        $surveyResult->save();

        return $surveyResult;
    }

    public static function statsCountResult($result, $results)
    {
        $value = $result->result_value["read"];
        if (!isset($results["read"])) {
            $results["read"] = 0;
        }
        if ($value) {
            $results["read"]++;
        }
        return $results;
    }
}
