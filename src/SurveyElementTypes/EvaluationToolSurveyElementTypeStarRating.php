<?php

namespace Twoavy\EvaluationTool\SurveyElementTypes;

use Illuminate\Http\Request;
use StdClass;
use Twoavy\EvaluationTool\Models\EvaluationToolSurvey;
use Twoavy\EvaluationTool\Models\EvaluationToolSurveyElement;
use Twoavy\EvaluationTool\Rules\SnakeCase;

class EvaluationToolSurveyElementTypeStarRating extends EvaluationToolSurveyElementTypeBase
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
            "question"          => $question,
            "numberOfStars"     => $this->faker->numberBetween(3, 10),
            "allowHalfSteps"    => false,
            "lowestValueLabel"  => ["de" => "niedrig", "en" => "low"],
            "middleValueLabel"  => ["de" => "mittel", "en" => "middle"],
            "highestValueLabel" => ["de" => "hoch", "en" => "high"],
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

    /**
     * @param EvaluationToolSurveyElement $surveyElement
     * @return array
     */
    public static function prepareResultRules(EvaluationToolSurveyElement $surveyElement): array
    {
        return [
            "result_value.rating" => [
                'required',
                'numeric',
                'min:1',
                'max:' . $surveyElement->params->numberOfStars,
            ],
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
            'params.numberOfStars'       => [
                'required',
                'numeric',
                'min:3',
                'max:10',
            ],
            'params.question'            => 'required|array',
            'params.question.*'          => 'min:1|max:200',
            'languageKeys.*'             => 'required|exists:evaluation_tool_survey_languages,code',
            'params.allowHalfSteps'      => 'boolean',
            'params.highestValueLabel'   => 'array',
            'params.highestValueLabel.*' => 'min:1|max:50',
            'params.middleValueLabel'    => 'array',
            'params.middleValueLabel.*'  => 'min:1|max:50',
            'params.lowestValueLabel'    => 'array',
            'params.lowestValueLabel.*'  => 'min:1|max:50',
            "params.meaningLowestValue"  => [
                "required",
                new SnakeCase()
            ],
            "params.meaningHighestValue" => [
                "required",
                new SnakeCase()
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
        if ($request->result_based_next_steps && is_array($request->result_based_next_steps) && !empty($request->result_based_next_steps)) {

            $usedRange = [];
            foreach ($request->result_based_next_steps as $resultBasedNextStep) {
                $range = $resultBasedNextStep["end"] - $resultBasedNextStep["start"];
                $i     = 0;
                while ($i < $range + 1) {
                    $nextIndex = $resultBasedNextStep["start"] + $i;

                    // check if value is allow in range (1 to numberOfStars)
                    if ($nextIndex < 1 || $nextIndex > $surveyElement->params->numberOfStars) {
                        abort(409, "value " . $nextIndex . " is out of range (1 to " . $surveyElement->params->numberOfStars . ")");
                    }

                    // check if value is already in range
                    if (in_array($nextIndex, $usedRange)) {
                        abort(409, "value " . $nextIndex . " already in range");
                    }

                    $usedRange[] = $nextIndex;
                    $i++;
                }
            }
        }
        return true;
    }
}
