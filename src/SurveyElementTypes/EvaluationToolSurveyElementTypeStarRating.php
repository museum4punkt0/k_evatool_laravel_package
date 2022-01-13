<?php

namespace Twoavy\EvaluationTool\SurveyElementTypes;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use StdClass;
use Twoavy\EvaluationTool\Helpers\EvaluationToolHelper;
use Twoavy\EvaluationTool\Models\EvaluationToolSurveyElement;
use Twoavy\EvaluationTool\Models\EvaluationToolSurveyLanguage;
use Twoavy\EvaluationTool\Models\EvaluationToolSurveyStep;
use Twoavy\EvaluationTool\Models\EvaluationToolSurveyStepResult;
use Twoavy\EvaluationTool\Rules\SnakeCase;

class EvaluationToolSurveyElementTypeStarRating extends EvaluationToolSurveyElementTypeBase
{

    const PARAMS_KEYS = ["question", "lowestValueLabel", "middleValueLabel", "highestValueLabel"];
    const LABEL_RULES = ["max:50"];

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
            'params.question.*'          => self::QUESTION_RULES,
            'languageKeys.*'             => 'required|exists:evaluation_tool_survey_languages,code',
            'params.allowHalfSteps'      => 'boolean',
            'params.highestValueLabel'   => 'array',
            'params.highestValueLabel.*' => self::LABEL_RULES,
            'params.middleValueLabel'    => 'array',
            'params.middleValueLabel.*'  => self::LABEL_RULES,
            'params.lowestValueLabel'    => 'array',
            'params.lowestValueLabel.*'  => self::LABEL_RULES,
            "params.meaningLowestValue"  => [
                "required",
                new SnakeCase(),
            ],
            "params.meaningHighestValue" => [
                "required",
                new SnakeCase(),
            ],
            "params.displayType"         => [
                "required",
                Rule::in(["stars", "grades", "neutral"])
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

    public static function getResultBasedNextStep(EvaluationToolSurveyStep $surveyStep)
    {
        if (isset($surveyStep->resultByUuid["rating"])) {
            $rating = $surveyStep->resultByUuid["rating"];
            if ($surveyStep->result_based_next_steps && !empty($surveyStep->result_based_next_steps)) {
                foreach ($surveyStep->result_based_next_steps as $nextStep) {
                    if ($nextStep->start <= $rating && $nextStep->end >= $rating) {
                        return $nextStep->stepId;
                    }
                }
            }
        }
        return $surveyStep->next_step_id;
    }

    public static function validateSurveyBasedLanguages(EvaluationToolSurveyElement $element): array
    {
        $keysToCheck = self::PARAMS_KEYS;

        return EvaluationToolHelper::checkMissingLanguages($element, $keysToCheck);
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
        $numberOfStars                    = $surveyResult->params['numberOfStars'];

        $resultValue                = new StdClass;
        $resultValue->rating        = rand(1, $numberOfStars);
        $surveyResult->result_value = $resultValue;

        $surveyResult->save();

        return $surveyResult;
    }

    public static function statsCountResult($result, $results)
    {
        $value = $result->result_value["rating"];

        // get element and meaning values
        $element       = $result->survey_step->survey_element;
        $numberOfStars = $element->params->numberOfStars;

        // initialize all options to 0
        $i = 1;
        while ($i <= $numberOfStars) {
            if (!isset($results[$i])) {
                $results[$i] = 0;
            }
            $i++;
        }

        if ($value >= 0 && $value <= $numberOfStars) {
            $results[$value]++;
        }

        return $results;
    }

    public static function checkCompleteLanguages($request)
    {
        EvaluationToolHelper::checkCompleteLanguages($request, self::PARAMS_KEYS);
    }

    public static function getExportDataHeaders(EvaluationToolSurveyStep $step, EvaluationToolSurveyLanguage $language, $position = 1): array
    {
        $numberOfOptions = 1;
        $exportData      = [];

        $exportData["elements"]   = [];
        $exportData["elements"][] = [
            "value" => $step->survey_element->survey_element_type->key,
            "span"  => $numberOfOptions,
        ];

        $exportData["question"]   = [];
        $exportData["question"][] = [
            "value" => $step->survey_element->params->question->{$language->code},
            "span"  => $numberOfOptions,
        ];

        $exportData["options"]   = [];
        $exportData["options"][] = [
            "value" => $step->survey_element->params->numberOfStars,
            "span"  => $numberOfOptions,
        ];

        return $exportData;
    }

    public static function getExportDataResult(EvaluationToolSurveyElement $element, EvaluationToolSurveyLanguage $language, $result, $position): array
    {
        return ["value" => $result->id, "position" => $position];
    }
}
