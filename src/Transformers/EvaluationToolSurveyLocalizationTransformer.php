<?php

namespace Twoavy\EvaluationTool\Transformers;

use League\Fractal\TransformerAbstract;
use Twoavy\EvaluationTool\Models\EvaluationToolSurveyLocalization;

class EvaluationToolSurveyLocalizationTransformer extends TransformerAbstract
{
    /**
     * Category transformer.
     *
     * @param EvaluationToolSurveyLocalization $surveLocalization
     * @return array
     */
    public function transform(EvaluationToolSurveyLocalization $surveyLocalization): array
    {
        return [
            "id"         => (int)$surveyLocalization->id,
            "model"      => (string)$surveyLocalization->model,
            "field"      => (string)$surveyLocalization->field,
            "value"      => (string)$surveyLocalization->value,
            "languageId" => (int)$surveyLocalization->language_id,
            "createdAt"  => $surveyLocalization->created_at,
            "updatedAt"  => $surveyLocalization->updated_at,
            "deletedAt"  => (string)$surveyLocalization->deleted_at,
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
            "id"         => "id",
            "model"      => "model",
            "languageId" => "language_id",
            "field"      => "field",
            "value"      => "value",
        ];
    }
}
