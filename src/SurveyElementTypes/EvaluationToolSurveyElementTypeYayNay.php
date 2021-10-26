<?php

namespace Twoavy\EvaluationTool\SurveyElementTypes;

use Illuminate\Http\Request;
use StdClass;
use Twoavy\EvaluationTool\Models\EvaluationToolSurveyElement;
use Twoavy\EvaluationTool\Rules\DuplicatesInArray;

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
            "question"   => $question,
            "trueValue"  => "accepted",
            "falseValue" => "declined",
            "trueLabel"  => ["de" => "ja", "en" => "yes", "fr" => "oui"],
            "falseLabel" => ["de" => "nein", "en" => "no", "fr" => "non"],
            "assetIds"  => [1, 2, 4]
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
            // TODO: assets length > 0
            'params.assetIds'  => 'required|array',
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
