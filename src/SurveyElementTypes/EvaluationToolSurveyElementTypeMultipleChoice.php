<?php

namespace Twoavy\EvaluationTool\SurveyElementTypes;

use Faker\Factory;
use Illuminate\Http\Request;
use StdClass;

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

        $faker = Factory::create();
        $minSelectable = $this->faker->numberBetween(1, 3);
        $maxSelectable = $this->faker->numberBetween($minSelectable, $minSelectable + $faker->numberBetween(1, 3));

        return [
            "question" => [
                "de" => "Frage",
                "en" => "Question",
                "fr" => "Question",
            ],
            "options" => [

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
                foreach ($request->params['options'] as $value) {
                    foreach ($value as $languageKey => $languageValue) {
                        $languageKeys[] = $languageKey;
                    }
                }
            }
        }
        $request->request->add(['languageKeys' => $languageKeys]);
    }

    /**
     * @return array
     */
    public static function rules(): array
    {
        $maxCount = 10;
        return [
            'params.question' => ['required', 'array', 'min:1'],
            'params.options' => ['required', 'array', 'min:1'],
            'params.options.*' => ['array'],
            'languageKeys.*' => ['required', 'exists:evaluation_tool_survey_languages,code'],
            'params.minSelectable' => ['integer', 'min:1', 'max:' . $maxCount],
            'params.maxSelectable' => ['integer', 'min:1', 'max:' . $maxCount, 'gte:min_elements'],
        ];
    }
}
