<?php

namespace Twoavy\EvaluationTool\SurveyElementTypes;

use Faker\Factory;
use Illuminate\Http\Request;
use StdClass;
use Twoavy\EvaluationTool\Models\EvaluationToolSurveyLanguage;

class EvaluationToolSurveyElementTypeSimpleText
{
    /**
     * @return array
     */
    public static function sampleParams(): array
    {

        $faker = Factory::create();

        $primaryLanguage    = EvaluationToolSurveyLanguage::where("default", true)->first();
        $secondaryLanguages = EvaluationToolSurveyLanguage::where("default", false)->get();

        $text                         = [];
        $text[$primaryLanguage->code] = $faker->words($faker->numberBetween(3, 50), true);
        foreach ($secondaryLanguages as $secondaryLanguage) {
            if ($faker->boolean(30)) {
                $text[$secondaryLanguage->code] = $faker->words($faker->numberBetween(3, 50), true);
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
            'params.options'      => ['required', 'array', 'min:1'],
            'params.options.*'    => ['array'],
            'languageKeys.*'      => ['required', 'exists:evaluation_tool_survey_languages,code'],
            'params.min_elements' => ['integer', 'min:1', 'max:' . $maxCount],
            'params.max_elements' => ['integer', 'min:1', 'max:' . $maxCount, 'gte:min_elements'],
        ];
    }
}
