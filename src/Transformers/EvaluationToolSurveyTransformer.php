<?php

namespace Twoavy\EvaluationTool\Transformers;

use League\Fractal\TransformerAbstract;
use Twoavy\EvaluationTool\Models\EvaluationToolSurvey;

class EvaluationToolSurveyTransformer extends TransformerAbstract
{
    /**
     * Category transformer.
     *
     * @param EvaluationToolSurvey $survey
     * @return array
     */
    public function transform(EvaluationToolSurvey $survey): array
    {
        return [
            "id"               => (int)$survey->id,
            "name"             => (string)$survey->name,
            "slug"             => (string)$survey->slug,
            "description"      => (string)$survey->description,
            "published"        => (bool)$survey->published,
            "publishUp"        => $survey->publish_up,
            "publishDown"      => $survey->publish_down,
            "adminLayout"      => $survey->admin_layout ?: [],
            "surveyStepsCount" => $survey->survey_steps_count,
            "createdAt"        => $survey->created_at,
            "updatedAt"        => $survey->updated_at,
            "deletedAt"        => (string)$survey->deleted_at,
            "links"            => [
                [
                    "rel"  => "self",
                    "href" => route("surveys.show", $survey->id)
                ],
                [
                    "rel"  => "survey-steps",
                    "href" => route("surveys.survey-steps.index", $survey->id)
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
            "id"          => "id",
            "name"        => "name",
            "slug"        => "slug",
            "description" => "description",
            "published"   => "published",
            "publishUp"   => "publish_up",
            "publishDown" => "publish_down",
            "adminLayout" => "admin_layout",
        ];
    }
}
