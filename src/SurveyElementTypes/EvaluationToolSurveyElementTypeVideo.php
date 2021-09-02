<?php

namespace Twoavy\EvaluationTool\SurveyElementTypes;

use Illuminate\Http\Request;
use StdClass;

class EvaluationToolSurveyElementTypeVideo extends EvaluationToolSurveyElementTypeBase
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
            "videoAssetId" => 1
        ];
    }

    public static function typeParams(): StdClass
    {
        return new StdClass();
    }

    public static function prepareRequest(Request $request)
    {

    }

    /**
     * @return array
     */
    public static function rules(): array
    {
        return [
            'params.videoAssetId' => 'required|exists:evaluation_tool_assets,id'
        ];
    }
}
