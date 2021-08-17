<?php

namespace Twoavy\EvaluationTool\Transformers;

use League\Fractal\TransformerAbstract;
use Twoavy\EvaluationTool\Models\EvaluationToolSurveyLanguage;

class EvaluationToolSurveyLanguageTransformer extends TransformerAbstract
{
    /**
     * Category transformer.
     *
     * @param EvaluationToolSurveyLanguage $surveyLanguage
     * @return array
     */
    public function transform(EvaluationToolSurveyLanguage $surveyLanguage): array
    {
        return [
            "id"               => (int)$surveyLanguage->id,
            "code"             => (string)$surveyLanguage->code,
            "sub_code"             => (string)$surveyLanguage->sub_code,
            "title"             => (string)$surveyLanguage->title,
            "default"             => (bool)$surveyLanguage->default,
            "published"             => (bool)$surveyLanguage->published,
            "createdAt"        => $surveyLanguage->created_at,
            "updatedAt"        => $surveyLanguage->updated_at,
            "deletedAt"        => (string)$surveyLanguage->deleted_at,
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
            "code"        => "code",
            "sub_code"    => "sub_code",
            "title"       => "title",
            "default"     => "default",
            "published"     => "published"
        ];
    }
}
