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
                "en" => "Question",
                "fr" => "Question",
            ],
            "options"       => [
                ["value" => "option_1", "labels" => ["de" => "option 1", "en" => "option 1", "fr" => "option 1"]],
                ["value" => "option_2", "labels" => ["de" => "option 2", "en" => "option 2", "fr" => "option 2"]],
                ["value" => "option_3", "labels" => ["de" => "option 3", "en" => "option 3", "fr" => "option 3"]],
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
            'result_value.selected'   => [
                'required',
                'array',
                'min:' . $minSelectable,
                'max:' . $maxSelectable,
            ],
            'result_value.selected.*' => [
                'in:' . implode(',', $possibleOptions),
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
            'params.question'         => ['required', 'array', 'min:1'],
            'params.question.*'       => self::QUESTION_RULES,
            'params.options'          => ['required', 'array', 'min:2', 'max:' . $maxCount],
            'params.options.*'        => ['array'],
            'params.options.*.labels' => ["required", 'array'],
            'params.options.*.value'  => [
                "required",
                new SnakeCase(),
            ],
            'languageKeys.*'          => ['required', 'exists:evaluation_tool_survey_languages,code'],
            'params.minSelectable'    => ['integer', 'min:1', 'lte:params.maxSelectable', 'lt:optionsCount'],
            'params.maxSelectable'    => ['integer', 'gte:params.minSelectable', 'lte:optionsCount']
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
        if (isset($surveyStep->resultByUuid["value"])) {
            $value         = $surveyStep->resultByUuid["value"];
            $minSelectable = $surveyStep->params["minSelectable"];
            $maxSelectable = $surveyStep->params["maxSelectable"];
            if ($minSelectable == 1 && $maxSelectable == 1) {
                if ($surveyStep->result_based_next_steps && !empty($surveyStep->result_based_next_steps)) {
                    foreach ($surveyStep->result_based_next_steps as $nextStep) {
                        if ($nextStep->value == $value) {
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
            array_push($randomArray, $value['value']);
        }

        $resultValue->selected      = $randomArray;
        $surveyResult->result_value = $resultValue;

        $surveyResult->save();

        return $surveyResult;
    }

    public static function statsCountResult($result, $results)
    {
        $values       = $result->result_value["selected"];
        $options      = $result->params["options"];
        $optionValues = array_column($options, "value");

        foreach ($values as $value) {
            if (in_array($value, $optionValues)) {
                if (!isset($results[$value])) {
                    $results[$value] = 0;
                }
                $results[$value]++;
            }
        }

        return $results;
    }

    public static function checkCompleteLanguages($request)
    {
        EvaluationToolHelper::checkCompleteLanguages($request, self::PARAMS_KEYS);
    }
}
