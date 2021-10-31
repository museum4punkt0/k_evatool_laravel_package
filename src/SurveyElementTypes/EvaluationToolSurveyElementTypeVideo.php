<?php

namespace Twoavy\EvaluationTool\SurveyElementTypes;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use StdClass;
use Twoavy\EvaluationTool\Models\EvaluationToolAsset;
use Twoavy\EvaluationTool\Models\EvaluationToolSurveyElement;
use Twoavy\EvaluationTool\Models\EvaluationToolSurveyStep;
use Twoavy\EvaluationTool\Rules\IsMediaType;

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
            "videoAssetId" => EvaluationToolAsset::where("mime", "LIKE", 'video/%')->first()->id,
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
        return [];
    }

    /**
     * @return array
     */
    public static function rules(): array
    {
        return [
            'params.videoAssetId' => [
                "required",
                "exists:evaluation_tool_assets,id",
                new IsMediaType("video")
            ]
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

    public static function validateTimeBasedSteps(Request $request, EvaluationToolSurveyElement $surveyElement): bool
    {
        $surveyId = Route::current()->parameters["survey"]->id;

        if ($request->has("time_based_steps") && is_array($request->time_based_steps)) {
            foreach ($request->time_based_steps as $timeBasedStep) {
                if (!$step = EvaluationToolSurveyStep::find($timeBasedStep["stepId"])) {
                    abort(409, "step does not exist");
                }
                if ($step->survey_id != $surveyId) {
                    abort(409, "survey id does not match time based step survey id");
                }
            }
        }
        return true;
    }

    public static function prepareResultRequest(): bool
    {
        return true;
    }
}
