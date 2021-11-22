<?php

namespace Twoavy\EvaluationTool\SurveyElementTypes;

use Illuminate\Http\Request;
use StdClass;
use Twoavy\EvaluationTool\Models\EvaluationToolSurveyElement;
use Twoavy\EvaluationTool\Models\EvaluationToolSurveyStepResult;
use Twoavy\EvaluationTool\Rules\SnakeCase;

class EvaluationToolSurveyElementTypeBinary extends EvaluationToolSurveyElementTypeBase
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

        $question = [];
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
            "falseLabel" => ["de" => "nein", "en" => "no", "fr" => "non"],
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
        $request->request->add(['languageKeys' => $languageKeys]);
    }

    public static function prepareResultRules(EvaluationToolSurveyElement $surveyElement): array
    {
        $trueValue = $surveyElement->params->trueValue;
        $falseValue = $surveyElement->params->falseValue;
        return [
            "result_value.value" => ['required', 'in:' . $trueValue . ',' . $falseValue],
        ];
    }

    /**
     * @return array
     */
    public static function rules(): array
    {
        return [
            'params.question' => 'required|array',
            'params.question.*' => 'min:1|max:200',
            'languageKeys.*' => 'required|exists:evaluation_tool_survey_languages,code',
            'params.trueValue' => ["required", "min:1", "max:20", new SnakeCase()],
            'params.falseValue' => ["required", "min:1", "max:20", new SnakeCase()],
        ];
    }

    public static function prepareResultRequest(): bool
    {
        return true;
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
        $surveyResult = new EvaluationToolSurveyStepResult();
        $surveyResult->session_id = $uuid;
        $surveyResult->demo = true;
        $surveyResult->survey_step_id = $surveyStep->id;
        $surveyResult->result_language_id = $languageId;
        $surveyResult->answered_at = $timestamp;
        $surveyResult->params = $surveyStep->survey_element->params;
        $binaryValues = array($surveyResult->params['trueValue'], $surveyResult->params['falseValue']);

        $resultValue = new StdClass;
        $resultValue->value = $binaryValues[array_rand($binaryValues, 1)];
        $surveyResult->result_value = $resultValue;

        $surveyResult->save();
    }
    public static function statsCountResult($result, $results): void
    {
        $value = $result->result_value["value"];
        if(in_array($value, array($result->params['trueValue'], $result->params['falseValue']))){
            $value = $result->result_value["value"];
            if (!isset($results->$value)) {
                $results->$value = 0;
            }
            $results->$value++;
        }
    }
}
