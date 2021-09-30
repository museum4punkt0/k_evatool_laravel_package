<?php

namespace Twoavy\EvaluationTool\Transformers;

use League\Fractal\TransformerAbstract;
use Twoavy\EvaluationTool\Models\EvaluationToolSurveyStep;

class EvaluationToolSurveyStepTransformer extends TransformerAbstract
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
            "name"                 => (string)$surveyStep->name,
            "surveyId"             => (int)$surveyStep->survey_id,
            "surveyElementId"      => (int)$surveyStep->survey_element_id,
            "surveyElementType"    => (string)$surveyStep->survey_element->survey_element_type->key,
            "nextStepId"           => $surveyStep->next_step_id ? (int)$surveyStep->next_step_id : null,
            "timeBasedSteps"       => $surveyStep->time_based_steps,
            "resultBasedNextSteps" => $surveyStep->result_based_next_steps,
            "parentStepId"         => $surveyStep->parent_step_id,
            "published"            => (bool)$surveyStep->published,
            "publishUp"            => $surveyStep->publish_up,
            "publishDown"          => $surveyStep->publish_down,
            "group"                => (string)$surveyStep->group,
            "allowSkip"            => (bool)$surveyStep->allow_skip,
            "createdAt"            => $surveyStep->created_at,
            "updatedAt"            => $surveyStep->updated_at,
            "deletedAt"            => (string)$surveyStep->deleted_at,
            "links"                => [
                [
                    "rel"  => "self",
                    "href" => route("surveys.steps.show", [$surveyStep->survey_id, $surveyStep->id])
                ],
                [
                    "rel"  => "survey",
                    "href" => route("surveys.show", $surveyStep->survey_id)
                ],
                [
                    "rel"  => "surveyElement",
                    "href" => route("survey-elements.show", $surveyStep->survey_element_id)
                ],
            ]
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
            "parentStepId"         => "parent_step_id",
            "group"                => "group",
            "allowSkip"            => "allow_skip",
            "published"            => "published",
            "publishUp"            => "publish_up",
            "publishDown"          => "publish_down",
        ];
    }
}
