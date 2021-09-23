<?php

namespace Twoavy\EvaluationTool\SurveyElementTypes;

use Illuminate\Http\Request;
use StdClass;
use Twoavy\EvaluationTool\Models\EvaluationToolSurveyElement;

class EvaluationToolSurveyElementTypeEmoji extends EvaluationToolSurveyElementTypeBase
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
            "emojis" => [
                [
                    "type" => "😊",
                    "meaning" => "great",
                ],
                [
                    "type" => "😠",
                    "meaning" => "angry",
                ],
                [
                    "type" => "😎",
                    "meaning" => "cool",
                ],
            ],
        ];
    }

    public static function typeParams(): StdClass
    {
        return new StdClass();
    }

    public static function prepareRequest(Request $request)
    {
        $meanings = [];
        $types = [];
        if ($request->has("params.emojis")) {
            foreach ($request->params["emojis"] as $emoji) {
                $meanings[] = $emoji["meaning"];
                $types[] = $emoji["type"];
            }
        }
        $request->request->add([
            "meanings" => $meanings,
            "types" => $types,
        ]);
    }

    public static function prepareResultRules(EvaluationToolSurveyElement $surveyElement)
    {
        $emojis = $surveyElement->params['emojis'];
        $meanings = [];
        foreach ($emojis as $key => $value) {
            array_push($meanings, $value['meaning']);
        }
        $rules = [
            "result_value.meaning" => ['required', 'in:' . implode(',', $meanings)],
        ];
        return $rules;
    }

    /**
     * @return array
     */
    public static function rules(): array
    {
        return [
            'params.emojis' => [
                'required',
                'array',
                'min:1',
                'max:10',
            ],
            "meanings.*" => "min:1|max:20",
            "types.*" => "min:1|max:1",
        ];
    }
}
