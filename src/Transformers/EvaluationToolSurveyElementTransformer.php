<?php

namespace Twoavy\EvaluationTool\Transformers;

use League\Fractal\TransformerAbstract;
use Twoavy\EvaluationTool\Models\EvaluationToolSurveyElement;

class EvaluationToolSurveyElementTransformer extends TransformerAbstract
{
    /**
     * Category transformer.
     *
     * @param EvaluationToolSurveyElement $surveyElement
     * @return array
     */
    public function transform(EvaluationToolSurveyElement $surveyElement): array
    {
        return [
            "id"                => (int)$surveyElement->id,
            "name"              => (string)$surveyElement->name,
            "description"       => (string)$surveyElement->description,
            //            "surveyElementTypeId" => (int)$surveyElement->survey_element_type_id,
            "surveyElementType" => (string)$surveyElement->survey_element_type->key,
            "surveyStepsCount"  => (int)$surveyElement->survey_steps_count,
            "params"            => $surveyElement->params,
            "createdAt"         => $surveyElement->created_at,
            "updatedAt"         => $surveyElement->updated_at,
            "deletedAt"         => (string)$surveyElement->deleted_at,
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
            "id"                  => "id",
            "name"                => "name",
            "description"         => "descriptoin",
            "surveyElementTypeId" => "survey_element_type_id",
            "surveyElementType"   => "survey_element_type",
            "params"              => "params",
            "published"           => "published",
            "publishUp"           => "publish_up",
            "publishDown"         => "publish_down",
        ];
    }
}
