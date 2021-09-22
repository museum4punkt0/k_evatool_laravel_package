<?php

namespace Twoavy\EvaluationTool\Transformers;

use League\Fractal\TransformerAbstract;
use Twoavy\EvaluationTool\Models\EvaluationToolSurveyStep;

class EvaluationToolSurveyStepResultCombinedTransformer extends TransformerAbstract
{
    /**
     * Category transformer.
     *
     * @param EvaluationToolSurveyStep $surveyStep
     * @return array
     */
    public function transform(EvaluationToolSurveyStep $surveyStep): array
    {
        return [
            "id"                   => (int)$surveyStep->id,
            "uuid"                 => request()->uuid,
            "surveyElementType"    => (string)$surveyStep->survey_element->survey_element_type->key,
            "params"               => $surveyStep->survey_element->params,
            "results"              => $surveyStep->survey_results,
            "resultsByUuid"              => $surveyStep->survey_results_by_uuid,
            "sampleResultPayload"  => $surveyStep->sampleResultPayload,
            //            "name"                 => (string)$surveyStep->name,
            //            "surveyId"             => (int)$surveyStep->survey_id,
            //            "surveyElementId"      => (int)$surveyStep->survey_element_id,
            "nextStepId"           => $surveyStep->next_step_id ? (int)$surveyStep->next_step_id : null,
            "timeBasedSteps"       => (array)$surveyStep->time_based_steps,
            "resultBasedNextSteps" => $surveyStep->result_based_next_steps,
            /*"published"            => (bool)$surveyStep->published,
            "publishUp"            => $surveyStep->publish_up,
            "publishDown"          => $surveyStep->publish_down,*/
            "group"                => (string)$surveyStep->group,
            "allowSkip"            => (bool)$surveyStep->allow_skip,
            /*"links"                => [
                [
                    "rel"  => "self",
                    "href" => route("surveys.survey-steps.show", [$surveyStep->survey_id, $surveyStep->id])
                ],
                [
                    "rel"  => "survey",
                    "href" => route("surveys.show", $surveyStep->survey_id)
                ],
                [
                    "rel"  => "surveyElement",
                    "href" => route("survey-elements.show", $surveyStep->survey_element_id)
                ],
            ]*/
        ];
    }

    public static function originalAttribute($index): ?string
    {
        $attributes = self::attributes();
        return isset($attributes[$index]) ? $attributes[$index] : null;
    }

    public static function transformedAttribute($index): ?string
    {
        $attributes = array_flip(self::attributes());
        return isset($attributes[$index]) ? $attributes[$index] : null;
    }

    public static function attributes(): array
    {
        return [
            "id"                   => "id",
            "name"                 => "name",
            "surveyId"             => "survey_id",
            "surveyElementId"      => "survey_element_id",
            "nextStepId"           => "next_step_id",
            "timeBasedSteps"       => "time_based_steps",
            "resultBasedNextSteps" => "result_based_next_steps",
            "group"                => "group",
            "allowSkip"            => "allow_skip",
            "published"            => "published",
            "publishUp"            => "publish_up",
            "publishDown"          => "publish_down",
        ];
    }
}
