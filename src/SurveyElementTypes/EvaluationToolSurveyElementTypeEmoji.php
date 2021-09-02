<?php

namespace Twoavy\EvaluationTool\SurveyElementTypes;

use Faker\Factory;
use Illuminate\Http\Request;
use StdClass;
use Twoavy\EvaluationTool\Helpers\EvaluationToolHelper;
use Twoavy\EvaluationTool\Models\EvaluationToolSurveyLanguage;

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
                    "type"    => "😊",
                    "meaning" => "great",
                ],
                [
                    "type"    => "😠",
                    "meaning" => "angry",
                ],
                [
                    "type"    => "😎",
                    "meaning" => "cool",
                ]
            ]
        ];
    }

    public static function typeParams(): StdClass
    {
        return new StdClass();
    }

    public static function prepareRequest(Request $request)
    {
        $meanings = [];
        $types    = [];
        if ($request->has("params.emojis")) {
            foreach ($request->params["emojis"] as $emoji) {
                $meanings[] = $emoji["meaning"];
                $types[]    = $emoji["type"];
            }
        }
        $request->request->add([
            "meanings" => $meanings,
            "types"    => $types
        ]);
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
                'max:10'
            ],
            "meanings.*"    => "min:1|max:20",
            "types.*"       => "min:1|max:1"
        ];
    }
}
