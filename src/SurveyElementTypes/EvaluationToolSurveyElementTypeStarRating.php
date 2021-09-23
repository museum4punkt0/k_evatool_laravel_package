<?php

namespace Twoavy\EvaluationTool\SurveyElementTypes;

use Illuminate\Http\Request;
use StdClass;
use Twoavy\EvaluationTool\Models\EvaluationToolSurveyElement;

class EvaluationToolSurveyElementTypeStarRating extends EvaluationToolSurveyElementTypeBase
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

        $question                               = [];
        $question[$this->primaryLanguage->code] = $this->faker->words($this->faker->numberBetween(3, 20), true);
        foreach ($this->secondaryLanguages as $secondaryLanguage) {
            if ($this->faker->boolean(60)) {
                $question[$secondaryLanguage->code] = $this->faker->words($this->faker->numberBetween(3, 20), true);
            }
        }

        return [
            "question"       => $question,
            "numberOfSteps"  => $this->faker->numberBetween(3, 10),
            "allowHalfSteps" => $this->faker->boolean(20),
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
                foreach ($request->params['question'] as $key => $value) {
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
        return [
            'params.numberOfSteps'  => [
                'required',
                'numeric',
                'min:3',
                'max:10'
            ],
            'params.question'       => 'required|array',
            'params.question.*'     => 'min:1|max:200',
            'languageKeys.*'        => 'required|exists:evaluation_tool_survey_languages,code',
            'params.allowHalfSteps' => 'boolean',
        ];
    }
}
