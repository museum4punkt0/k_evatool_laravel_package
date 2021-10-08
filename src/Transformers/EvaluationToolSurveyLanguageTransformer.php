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
            "id"        => (int)$surveyLanguage->id,
            "code"      => (string)$surveyLanguage->code,
            "subCode"   => (string)$surveyLanguage->sub_code,
            "title"     => (string)$surveyLanguage->title,
            "default"   => (bool)$surveyLanguage->default,
            "published" => (bool)$surveyLanguage->published,
            "createdAt" => $surveyLanguage->created_at,
            "createdBy" => $surveyLanguage->created_by_user ? $surveyLanguage->created_by_user->name : null,
            "updatedAt" => $surveyLanguage->updated_at,
            "updatedBy" => $surveyLanguage->updated_by_user ? $surveyLanguage->updated_by_user->name : null,
            "deletedAt" => $surveyLanguage->deleted_at,
            "deletedBy" => $surveyLanguage->deleted_by_user ? $surveyLanguage->deleted_by_user->name : null,
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
            "id"        => "id",
            "code"      => "code",
            "subCode"   => "sub_code",
            "title"     => "title",
            "default"   => "default",
            "published" => "published"
        ];
    }
}
