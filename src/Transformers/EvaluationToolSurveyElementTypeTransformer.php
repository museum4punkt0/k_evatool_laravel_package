<?php

namespace Twoavy\EvaluationTool\Transformers;

use League\Fractal\TransformerAbstract;
use Twoavy\EvaluationTool\Models\EvaluationToolSurveyElementType;

class EvaluationToolSurveyElementTypeTransformer extends TransformerAbstract
{
    /**
     * Category transformer.
     *
     * @param EvaluationToolSurveyStep $surveyElementType
     * @return array
     */
    public function transform(EvaluationToolSurveyElementType $surveyElementType): array
    {
        return [
            "id"               => (int)$surveyElementType->id,
            "key"               => (string)$surveyElementType->key,
            "name"             => (string)$surveyElementType->name,
            "description"      => (string)$surveyElementType->description,
            "params"           => $surveyElementType->params,
            "createdAt"        => $surveyElementType->created_at,
            "updatedAt"        => $surveyElementType->updated_at,
            "deletedAt"        => (string)$surveyElementType->deleted_at,
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
            "description" => "descriptoin",
            "params"      => "params"
        ];
    }
}
