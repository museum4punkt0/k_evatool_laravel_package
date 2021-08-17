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
            "id"               => (int)$surveyStep->id,
            "name"             => (string)$surveyStep->name,
            "published"        => (bool)$surveyStep->published,
            "publishUp"        => $surveyStep->publish_up,
            "publishDown"      => $surveyStep->publish_down,
            "createdAt"        => $surveyStep->created_at,
            "updatedAt"        => $surveyStep->updated_at,
            "deletedAt"        => (string)$surveyStep->deleted_at,
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
            "published"   => "published",
            "publishUp"   => "publish_up",
            "publishDown" => "publish_down",
        ];
    }
}
