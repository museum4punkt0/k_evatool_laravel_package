<?php

namespace Twoavy\EvaluationTool\SurveyElementTypes;

use Illuminate\Http\Request;
use StdClass;
use Twoavy\EvaluationTool\Models\EvaluationToolSurveyElement;
use Twoavy\EvaluationTool\Models\EvaluationToolSurveyStepResult;
use Twoavy\EvaluationTool\Rules\Emoji;
use Twoavy\EvaluationTool\Rules\SnakeCase;

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
        /*$meanings = [];
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
        ]);*/

        $languageKeys = [];

        if ($request->has('params.question')) {
            if (is_array($request->params['question'])) {
                foreach ($request->params['question'] as $questionLanguageKey => $question) {
                    $languageKeys[] = $questionLanguageKey;
                }
            }
        }

        $request->request->add(['languageKeys' => $languageKeys]);
    }

    public static function prepareResultRules(EvaluationToolSurveyElement $surveyElement): array
    {
        $emojis   = $surveyElement->params->emojis;
        $meanings = [];
        foreach ($emojis as $value) {
            array_push($meanings, $value->meaning);
        }

        return [
            "result_value.meaning" => ['required', 'in:' . implode(',', $meanings)],
        ];
    }

    public static function prepareResultRequest(): bool
    {
        return true;
    }

    /**
     * @return array
     */
    public static function rules(): array
    {
        return [
            'params.question'         => ['required', 'array', 'min:1'],
            'params.question.*'       => ['required', 'min:1', 'max:200'],
            'params.emojis'           => [
                'required',
                'array',
                'min:1',
                'max:10',
            ],
            "params.emojis.*.type"    => ["min:1", "max:20", new Emoji()],
            "params.emojis.*.meaning" => ["min:1", "max:20", new SnakeCase()],
            'languageKeys.*'          => ['required', 'exists:evaluation_tool_survey_languages,code'],
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

    public static function seedResult($surveyStep, $uuid, $languageId, $timestamp)
    {
        $surveyResult                     = new EvaluationToolSurveyStepResult();
        $surveyResult->session_id         = $uuid;
        $surveyResult->demo               = true;
        $surveyResult->survey_step_id     = $surveyStep->id;
        $surveyResult->result_language_id = $languageId;
        $surveyResult->answered_at        = $timestamp;
        $surveyResult->params             = $surveyStep->survey_element->params;
        $emojisArray                      = collect($surveyResult->params['emojis'])->random();

        $resultValue                = new StdClass;
        $resultValue->meaning       = $emojisArray['meaning'];
        $surveyResult->result_value = $resultValue;

        $surveyResult->save();
    }
}
