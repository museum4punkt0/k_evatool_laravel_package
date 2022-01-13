<?php

namespace Twoavy\EvaluationTool\SurveyElementTypes;

use Illuminate\Http\Request;
use StdClass;
use Twoavy\EvaluationTool\Helpers\EvaluationToolHelper;
use Twoavy\EvaluationTool\Models\EvaluationToolSurveyElement;
use Twoavy\EvaluationTool\Models\EvaluationToolSurveyStepResult;
use Twoavy\EvaluationTool\Rules\DuplicatesInArray;
use Twoavy\EvaluationTool\Rules\IsMediaType;
use Twoavy\EvaluationTool\Rules\SnakeCase;

class EvaluationToolSurveyElementTypeYayNay extends EvaluationToolSurveyElementTypeBase
{

    const PARAMS_KEYS = ["question", "trueLabel", "falseLabel"];

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
            "question"   => $question,
            "trueValue"  => "accepted",
            "falseValue" => "declined",
            "trueLabel"  => ["de" => "ja", "en" => "yes"],
            "falseLabel" => ["de" => "nein", "en" => "no"],
            "assetIds"   => [1, 2, 4],
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
                    $languageKeys["question_" . $key] = $key;
                }
            }
        }

        if ($request->has('params.trueLabel')) {
            if (is_array($request->params['trueLabel'])) {
                foreach ($request->params['trueLabel'] as $key => $value) {
                    $languageKeys["trueLabel_" . $key] = $key;
                }
            }
        }

        if ($request->has('params.falseLabel')) {
            if (is_array($request->params['falseLabel'])) {
                foreach ($request->params['falseLabel'] as $key => $value) {
                    $languageKeys["falseLabel_" . $key] = $key;
                }
            }
        }

        $request->request->add(['languageKeys' => $languageKeys]);
    }

    public static function prepareResultRules(EvaluationToolSurveyElement $surveyElement): array
    {
        $trueValue  = $surveyElement->params->trueValue;
        $falseValue = $surveyElement->params->falseValue;

        return [
            "result_value.images"         => ['required', 'array'],
            "result_value.images.*.asset" => ['required', 'in:' . implode(",", $surveyElement->params->assetIds)],
            "result_value.images.*.value" => ['required', 'in:' . $trueValue . ',' . $falseValue],
            "asset_ids"                   => ['required', new DuplicatesInArray],
        ];
    }

    public static function prepareResultRequest(EvaluationToolSurveyElement $surveyElement)
    {
        $imageIds = [];
        if (request()->has("result_value") && isset(request()->result_value["images"]) && is_array(request()->result_value["images"])) {
            foreach (request()->result_value["images"] as $image) {
                $imageIds[] = $image["asset"];
            }
        }
        request()->request->add(["asset_ids" => $imageIds]);
    }

    /**
     * @return array
     */
    public static function rules(): array
    {
        return [
            'params.question'   => 'required|array',
            'params.assetIds'   => ["required", "array", new DuplicatesInArray],
            'params.assetIds.*' => [
                "exists:evaluation_tool_assets,id",
                new IsMediaType("image"),
            ],
            'params.question.*' => self::QUESTION_RULES,
            'languageKeys.*'    => 'required|exists:evaluation_tool_survey_languages,code',
            'params.trueValue'  => ["required", new SnakeCase()],
            'params.falseValue' => ["required", new SnakeCase()],
            'params.trueLabel'  => ["required", "array"],
            'params.falseLabel' => ["required", "array"],
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

    public static function validateSurveyBasedLanguages(EvaluationToolSurveyElement $element): array
    {
        return EvaluationToolHelper::checkMissingLanguages($element, self::PARAMS_KEYS);
    }

    public static function seedResult($surveyStep, $uuid, $languageId, $timestamp): EvaluationToolSurveyStepResult
    {
        $surveyResult                     = new EvaluationToolSurveyStepResult();
        $surveyResult->session_id         = $uuid;
        $surveyResult->demo               = true;
        $surveyResult->survey_step_id     = $surveyStep->id;
        $surveyResult->result_language_id = $languageId;
        $surveyResult->answered_at        = $timestamp;
        $surveyResult->params             = $surveyStep->survey_element->params;
        $imagesArray                      = collect($surveyResult->params['assetIds']);
        $binaryValues                     = array($surveyResult->params['trueValue'], $surveyResult->params['falseValue']);

        $randomArray = array();
        foreach ($imagesArray as $id) {
            $assetResult          = array();
            $assetResult['asset'] = $id;
            $assetResult['value'] = $binaryValues[array_rand($binaryValues, 1)];
            array_push($randomArray, $assetResult);
        }
        $resultValue                = new StdClass;
        $resultValue->images        = $randomArray;
        $surveyResult->result_value = $resultValue;

        $surveyResult->save();

        return $surveyResult;
    }

    public static function statsCountResult($result, $results)
    {

        $element    = $result->survey_step->survey_element;
        $assetIds   = $element->params->assetIds;
        $trueValue  = $element->params->trueValue;
        $falseValue = $element->params->falseValue;

        // fill all values with 0 if not already set
        if (!isset($results)) {
            $results = [];
        }

        foreach ($assetIds as $assetId) {
            if (!in_array($assetId, array_column($results, 'assetId'))) {
                $results[] = [
                    "assetId"   => $assetId,
                    $trueValue  => 0,
                    $falseValue => 0
                ];
            }
        }

        foreach ($result->result_value["images"] as $image) {
            $value = $image["value"];

            if (in_array($value, array($result->params['trueValue'], $result->params['falseValue']))) {
                $index = array_search($image["asset"], array_column($results, 'assetId'));
                $results[$index][$value]++;
            }
        }

        return $results;
    }

    public static function checkCompleteLanguages($request)
    {
        EvaluationToolHelper::checkCompleteLanguages($request, self::PARAMS_KEYS);
    }
}
