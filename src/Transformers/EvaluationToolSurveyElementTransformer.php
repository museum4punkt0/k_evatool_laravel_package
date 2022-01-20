<?php

namespace Twoavy\EvaluationTool\Transformers;

use League\Fractal\TransformerAbstract;
use Twoavy\EvaluationTool\Models\EvaluationToolSurveyElement;
use Twoavy\EvaluationTool\SurveyElementTypes\EvaluationToolSurveyElementTypeSimpleText;

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
        $typeClassName = 'Twoavy\EvaluationTool\SurveyElementTypes\EvaluationToolSurveyElementType' . ucfirst($surveyElement->survey_element_type->key);

        return [
            "id"                => (int)$surveyElement->id,
            "name"              => (string)$surveyElement->name,
            "description"       => (string)$surveyElement->description,
            "surveyElementType" => (string)$surveyElement->survey_element_type->key,
            "surveyStepsCount"  => (int)$surveyElement->survey_steps_count,
            "surveysCount"      => $surveyElement->surveys->count(),
            "resultCount"       => (int)$surveyElement->survey_results_count,
            "demoResultCount"   => (int)$surveyElement->survey_demo_results_count,
            "hasResults"        => (boolean)$surveyElement->has_results,
            "params"            => $surveyElement->params,
            "createdAt"         => $surveyElement->created_at,
            "createdBy"         => $surveyElement->created_by_user ? $surveyElement->created_by_user->name : null,
            "updatedAt"         => $surveyElement->updated_at,
            "updatedBy"         => $surveyElement->updated_by_user ? $surveyElement->updated_by_user->name : null,
            "deletedAt"         => (string)$surveyElement->deleted_at,
            "deletedBy"         => $surveyElement->deleted_by_user ? $surveyElement->deleted_by_user->name : null,
            "missingLanguages"  => class_exists($typeClassName) && method_exists($typeClassName, "validateSurveyBasedLanguages") ? $typeClassName::validateSurveyBasedLanguages($surveyElement) : null
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
