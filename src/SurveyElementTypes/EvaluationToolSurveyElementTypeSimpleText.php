<?php

namespace Twoavy\EvaluationTool\SurveyElementTypes;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use StdClass;
use Twoavy\EvaluationTool\Helpers\EvaluationToolHelper;
use Twoavy\EvaluationTool\Models\EvaluationToolSurveyElement;
use Twoavy\EvaluationTool\Models\EvaluationToolSurveyLanguage;
use Twoavy\EvaluationTool\Models\EvaluationToolSurveyStep;
use Twoavy\EvaluationTool\Models\EvaluationToolSurveyStepResult;
use Twoavy\EvaluationTool\Rules\IsMediaType;

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
        return [
            'params.text'    => ['required', 'array', 'min:1'],
            'params.text.*'  => self::TEXT_RULES,
            'params.url'     => ['required', 'url'],
            'params.assetId' => [
                "exists:evaluation_tool_assets,id",
                new IsMediaType("image"),
            ],
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

    public static function checkCompleteLanguages($request)
    {
        EvaluationToolHelper::checkCompleteLanguages($request, ["text"]);
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
            "value" => $step->survey_element->params->text->{$language->code},
            "span"  => $numberOfOptions,
        ];

        $exportData["options"]   = [];
        $exportData["options"][] = [
            "value" => "read",
            "span"  => $numberOfOptions,
        ];

        return $exportData;
    }

    public static function getExportDataResult(EvaluationToolSurveyElement $element, EvaluationToolSurveyLanguage $language, $result, $position): array
    {
        return [
            ["value" => $result->result_value["read"] ? "x" : null, "position" => $position]
        ];
    }
}
