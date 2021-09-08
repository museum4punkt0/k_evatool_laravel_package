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
            "id"              => (int)$surveyStep->id,
            "name"            => (string)$surveyStep->name,
            "surveyId"        => (int)$surveyStep->survey_id,
            "surveyElementId" => (int)$surveyStep->survey_element_id,
            "nextStepId"      => $surveyStep->next_step_id ? (int)$surveyStep->next_step_id : null,
            "published"       => (bool)$surveyStep->published,
            "publishUp"       => $surveyStep->publish_up,
            "publishDown"     => $surveyStep->publish_down,
            "group"           => (string)$surveyStep->group,
            "allowSkip"       => (bool)$surveyStep->allow_skip,
            "createdAt"       => $surveyStep->created_at,
            "updatedAt"       => $surveyStep->updated_at,
            "deletedAt"       => (string)$surveyStep->deleted_at,
            "links"           => [
                [
                    "rel"  => "self",
                    "href" => route("surveys.survey-steps.show", [$surveyStep->survey_id, $surveyStep->id])
                ],
                [
                    "rel"  => "survey",
                    "href" => route("surveys.show", $surveyStep->survey_id)
                ],
                [
                    "rel"  => "survey_element",
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
            "id"              => "id",
            "name"            => "name",
            "surveyId"        => "survey_id",
            "surveyElementId" => "survey_element_id",
            "nextStepId"      => "next_step_id",
            "group"           => "group",
            "allowSkip"       => "allow_skip",
            "published"       => "published",
            "publishUp"       => "publish_up",
            "publishDown"     => "publish_down",
        ];
    }
}
