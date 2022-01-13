<?php

namespace Twoavy\EvaluationTool\SurveyElementTypes;

use Illuminate\Http\Request;
use StdClass;
use Twoavy\EvaluationTool\Helpers\EvaluationToolHelper;
use Twoavy\EvaluationTool\Models\EvaluationToolSurveyElement;
use Twoavy\EvaluationTool\Models\EvaluationToolSurveyLanguage;
use Twoavy\EvaluationTool\Models\EvaluationToolSurveyStep;
use Twoavy\EvaluationTool\Models\EvaluationToolSurveyStepResult;
use Twoavy\EvaluationTool\Rules\Emoji;
use Twoavy\EvaluationTool\Rules\SnakeCase;

class EvaluationToolSurveyElementTypeEmoji extends EvaluationToolSurveyElementTypeBase
{

    const PARAMS_KEYS = ["question"];

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
            "emojis" => [
                [
                    "type"    => "😊",
                    "meaning" => "great",
                ],
                [
                    "type"    => "😠",
                    "meaning" => "angry",
                ],
                [
                    "type"    => "😎",
                    "meaning" => "cool",
                ],
            ],
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
                foreach ($request->params['question'] as $questionLanguageKey => $question) {
                    $languageKeys[] = $questionLanguageKey;
                }
            }
        }

        $request->request->add(['languageKeys' => $languageKeys]);
    }

    public static function prepareResultRules(EvaluationToolSurveyElement $surveyElement): array
    {
        $emojis   = $surveyElement->params->emojis;
        $meanings = [];
        foreach ($emojis as $value) {
            array_push($meanings, $value->meaning);
        }

        return [
            "result_value.meaning" => ['required', 'in:' . implode(',', $meanings)],
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
            'params.question'         => ['required', 'array', 'min:1'],
            'params.question.*'       => self::QUESTION_RULES,
            'params.emojis'           => [
                'required',
                'array',
                'min:1',
                'max:10',
            ],
            "params.emojis.*.type"    => ["min:1", "max:20", new Emoji()],
            "params.emojis.*.meaning" => ["min:1", "max:20", new SnakeCase()],
            'languageKeys.*'          => ['required', 'exists:evaluation_tool_survey_languages,code'],
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

    public static function validateSurveyBasedLanguages(EvaluationToolSurveyElement $element): array
    {
        return EvaluationToolHelper::checkMissingLanguages($element, self::PARAMS_KEYS);
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
        $emojisArray                      = collect($surveyResult->params['emojis'])->random();

        $resultValue                = new StdClass;
        $resultValue->meaning       = $emojisArray['meaning'];
        $surveyResult->result_value = $resultValue;

        $surveyResult->save();

        return $surveyResult;
    }

    public static function statsCountResult($result, $results)
    {
        $value = $result->result_value["meaning"];

        // get element and meaning values
        $element  = $result->survey_step->survey_element;
        $emojis   = $element->params->emojis;
        $meanings = array_column($emojis, "meaning");

        // fill all values with 0 if not already set
        foreach ($meanings as $meaning) {
            if (!isset($results[$meaning])) {
                $results[$meaning] = 0;
            }
        }

        if (in_array($value, $meanings)) {
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
        $numberOfOptions = count($step->survey_element->params->emojis);
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

        $exportData["options"] = [];
        foreach ($step->survey_element->params->emojis as $emoji) {
            $exportData["options"][] = [
                "value" => $emoji->meaning,
                "span"  => 1
            ];
        }

        return $exportData;
    }
}
