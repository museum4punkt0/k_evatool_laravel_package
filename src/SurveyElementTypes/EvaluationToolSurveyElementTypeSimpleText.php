<?php

namespace Twoavy\EvaluationTool\SurveyElementTypes;

use Faker\Factory;
use Illuminate\Http\Request;
use StdClass;
use Twoavy\EvaluationTool\Helpers\EvaluationToolHelper;
use Twoavy\EvaluationTool\Models\EvaluationToolSurveyLanguage;
use Twoavy\EvaluationTool\Models\EvaluationToolSurveyElement;

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

    public static function prepareResultRules(EvaluationToolSurveyElement $surveyElement)
    {
        // $emojis = $surveyElement->params['emojis'];
        // $meanings = [];
        // foreach ($emojis as $key => $value) {
        // array_push($meanings, $value['meaning']);
        // }
        $rules = [
        ];
        return $rules;
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
}
