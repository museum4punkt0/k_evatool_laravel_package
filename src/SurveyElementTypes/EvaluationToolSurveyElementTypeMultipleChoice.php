<?php

namespace Twoavy\EvaluationTool\SurveyElementTypes;

use Faker\Factory;
use Illuminate\Http\Request;
use StdClass;
use Twoavy\EvaluationTool\Helpers\EvaluationToolHelper;
use Twoavy\EvaluationTool\Models\EvaluationToolSurveyElement;
use Twoavy\EvaluationTool\Models\EvaluationToolSurveyLanguage;
use Twoavy\EvaluationTool\Models\EvaluationToolSurveyStep;
use Twoavy\EvaluationTool\Models\EvaluationToolSurveyStepResult;
use Twoavy\EvaluationTool\Rules\SnakeCase;

class EvaluationToolSurveyElementTypeMultipleChoice extends EvaluationToolSurveyElementTypeBase
{

    const PARAMS_KEYS = ["question", "options.*.labels"];

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @return array
     */
    public function sampleParams(): array
    {

        $faker         = Factory::create();
        $minSelectable = $this->faker->numberBetween(1, 3);
        $maxSelectable = $this->faker->numberBetween($minSelectable, $minSelectable + $faker->numberBetween(1, 3));

        return [
            "question"      => [
                "de" => "Frage",
                "en" => "Question"
            ],
            "options"       => [
                ["value" => "option_1", "labels" => ["de" => "option 1", "en" => "option 1"]],
                ["value" => "option_2", "labels" => ["de" => "option 2", "en" => "option 2"]],
                ["value" => "option_3", "labels" => ["de" => "option 3", "en" => "option 3"]],
            ],
            "minSelectable" => $minSelectable,
            "maxSelectable" => $maxSelectable,
        ];
    }

    public static function typeParams(): StdClass
    {
        return new StdClass();
    }

    public static function prepareRequest(Request $request)
    {
        $languageKeys = [];
        if ($request->has('params.options')) {
            if (is_array($request->params['options'])) {
                foreach ($request->params['options'] as $option) {
                    if (array_key_exists("labels", $option)) {
                        if (is_array($option['labels'])) {
                            foreach ($option['labels'] as $labelLanguageKey => $labelValue) {
                                $languageKeys[] = $labelLanguageKey;
                            }
                        }
                    }
                }
            }
        }

        if ($request->has('params.question')) {
            if (is_array($request->params['question'])) {
                foreach ($request->params['question'] as $questionLanguageKey => $question) {
                    $languageKeys[] = $questionLanguageKey;
                }
            }
        }


        $request->request->add(['optionsCount' => count($request->params['options'])]);
        $request->request->add(['languageKeys' => $languageKeys]);
    }

    /**
     * @param EvaluationToolSurveyElement $surveyElement
     * @return array
     */
    public static function prepareResultRules(EvaluationToolSurveyElement $surveyElement): array
    {
        $possibleOptions = [];
        $options         = $surveyElement->params->options;
        $minSelectable   = $surveyElement->params->minSelectable;
        $maxSelectable   = $surveyElement->params->maxSelectable;
        foreach ($options as $option) {
            array_push($possibleOptions, $option->value);
        }

        return [
            'result_value.selected'           => [
                'required',
                'array',
                'min:' . $minSelectable,
                'max:' . $maxSelectable,
            ],
            'result_value.selected.*.value'   => [
                'in:' . implode(',', $possibleOptions),
            ],
            'result_value.selected.*.comment' => [
                'max:200',
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
//        print_r(request()->all());

        $maxCount = 20;
        return [
            'params.question'              => ['required', 'array', 'min:1'],
            'params.question.*'            => self::QUESTION_RULES,
            'params.options'               => ['required', 'array', 'min:2', 'max:' . $maxCount],
            'params.options.*'             => ['array'],
            'params.options.*.labels'      => ["required", 'array'],
            'params.options.*.value'       => [
                "required",
                new SnakeCase(),
            ],
            'params.options.*.commentable' => ['boolean'],
            'languageKeys.*'               => ['required', 'exists:evaluation_tool_survey_languages,code'],
            'params.minSelectable'         => ['integer', 'min:1', 'lte:params.maxSelectable', 'lt:optionsCount'],
            'params.maxSelectable'         => ['integer', 'gte:params.minSelectable', 'lte:optionsCount']
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

    public static function getResultBasedNextStep(EvaluationToolSurveyStep $surveyStep)
    {
        if (isset($surveyStep->resultByUuid["selected"])) {
            $value         = $surveyStep->resultByUuid["selected"];
            $minSelectable = $surveyStep->survey_element->params->minSelectable;
            $maxSelectable = $surveyStep->survey_element->params->maxSelectable;
            if ($minSelectable == 1 && $maxSelectable == 1) {
                if ($surveyStep->result_based_next_steps && !empty($surveyStep->result_based_next_steps)) {
                    foreach ($surveyStep->result_based_next_steps as $nextStep) {
                        if ($nextStep->value == $value[0]["value"]) {
                            return $nextStep->stepId;
                        }
                    }
                }
            }
        }
        return $surveyStep->next_step_id;
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

        $resultValue = new StdClass;

        $maxSelectable = $surveyResult->params['maxSelectable'];
        $minSelectable = $surveyResult->params['minSelectable'];
        $optionsArray  = collect($surveyResult->params['options'])->random(rand($minSelectable, $maxSelectable));
        $randomArray   = array();

        foreach ($optionsArray as $value) {
            array_push($randomArray, ["value" => $value['value']]);
        }

        $resultValue->selected      = $randomArray;
        $surveyResult->result_value = $resultValue;

        $surveyResult->save();

        return $surveyResult;
    }

    public static function statsCountResult($result, $results)
    {
        $values = $result->result_value["selected"];

        // get element and option values
        $element      = $result->survey_step->survey_element;
        $options      = $element->params->options;
        $optionValues = array_column($options, "value");

        // fill all values with 0 if not already set
        foreach ($optionValues as $optionValue) {
            if (!isset($results[$optionValue])) {
                $results[$optionValue] = 0;
            }
        }

        foreach ($values as $value) {
            if (in_array($value["value"], $optionValues)) {
                $results[$value["value"]]++;
            }
        }

        return $results;
    }

    public static function checkCompleteLanguages($request)
    {
        EvaluationToolHelper::checkCompleteLanguages($request, self::PARAMS_KEYS);
    }

    public static function getExportData(EvaluationToolSurveyElement $element, EvaluationToolSurveyLanguage $language): array
    {
        $numberOfOptions = count($element->params->options);
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

        $exportData["options"] = [];
        foreach ($element->params->options as $option) {
            $exportData["options"][] = [
                "value" => $option->labels->{$language->code},
                "span"  => 1
            ];
        }

        return $exportData;
    }
}
