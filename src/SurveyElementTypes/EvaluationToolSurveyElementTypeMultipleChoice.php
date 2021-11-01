<?php

namespace Twoavy\EvaluationTool\SurveyElementTypes;

use Faker\Factory;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use StdClass;
use Twoavy\EvaluationTool\Helpers\EvaluationToolHelper;
use Twoavy\EvaluationTool\Models\EvaluationToolSurvey;
use Twoavy\EvaluationTool\Models\EvaluationToolSurveyElement;
use Twoavy\EvaluationTool\Models\EvaluationToolSurveyStep;
use Twoavy\EvaluationTool\Rules\SnakeCase;

class EvaluationToolSurveyElementTypeMultipleChoice extends EvaluationToolSurveyElementTypeBase
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
            ]
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
            'params.question'         => ['required', 'array', 'min:1'],
            'params.options'          => ['required', 'array', 'min:1'],
            'params.options.*'        => ['array'],
            'params.options.*.labels' => ["required", 'array'],
            'params.options.*.value'  => [
                "required",
                new SnakeCase()
            ],
            'languageKeys.*'          => ['required', 'exists:evaluation_tool_survey_languages,code'],
            'params.minSelectable'    => ['integer', 'min:1', 'max:' . $maxCount],
//            'params.maxSelectable'    => ['integer', 'between:1,params.minSelectable', 'max:' . $maxCount],
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
}
