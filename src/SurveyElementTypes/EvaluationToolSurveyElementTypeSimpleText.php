<?php

namespace Twoavy\EvaluationTool\SurveyElementTypes;

use Faker\Factory;
use Illuminate\Http\Request;
use StdClass;
use Twoavy\EvaluationTool\Helpers\EvaluationToolHelper;
use Twoavy\EvaluationTool\Models\EvaluationToolSurveyLanguage;

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
