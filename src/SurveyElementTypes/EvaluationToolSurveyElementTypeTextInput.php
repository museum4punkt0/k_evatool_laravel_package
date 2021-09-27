<?php

namespace Twoavy\EvaluationTool\SurveyElementTypes;

use Illuminate\Http\Request;
use StdClass;
use Twoavy\EvaluationTool\Models\EvaluationToolSurveyElement;

class EvaluationToolSurveyElementTypeTextInput extends EvaluationToolSurveyElementTypeBase
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
        return [
            "question" => [
                "de" => "Frage",
                "en" => "Question",
                "fr" => "Question",
            ]
        ];
    }

    public static function typeParams(): StdClass
    {
        return new StdClass();
    }

    public static function prepareRequest(Request $request)
    {

    }
    public static function prepareResultRules(EvaluationToolSurveyElement $surveyElement): array
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
            'params.question' => ['required', 'array', 'min:1'],
        ];
    }
}
