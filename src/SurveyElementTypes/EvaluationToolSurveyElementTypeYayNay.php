<?php

namespace Twoavy\EvaluationTool\SurveyElementTypes;

use Illuminate\Http\Request;
use StdClass;
use Twoavy\EvaluationTool\Models\EvaluationToolSurveyElement;

class EvaluationToolSurveyElementTypeYayNay extends EvaluationToolSurveyElementTypeBase
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
            "question" => $question,
            "trueValue" => "accepted",
            "falseValue" => "declined",
            "trueLabel" => ["de" => "ja", "en" => "yes", "fr" => "oui"],
            "falseLabel" => ["de" => "nein", "en" => "no", "fr" => "non"]
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
        $trueValue = $surveyElement->params['trueValue'];
        $falseValue = $surveyElement->params['falseValue'];
        $rules = [
            "result_value.value" => ['required', 'in:' . $trueValue . ',' . $falseValue],
        ];
        return $rules;
    }

    /**
     * @return array
     */
    public static function rules(): array
    {
        return [
            'params.question'   => 'required|array',
            'params.question.*' => 'min:1|max:200',
            'languageKeys.*'    => 'required|exists:evaluation_tool_survey_languages,code',
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
