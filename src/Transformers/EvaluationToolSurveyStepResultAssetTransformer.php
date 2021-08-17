<?php

namespace Twoavy\EvaluationTool\Transformers;

use League\Fractal\TransformerAbstract;
use Twoavy\EvaluationTool\Models\EvaluationToolSurveyStepResultAsset;

class EvaluationToolSurveyStepResultAssetTransformer extends TransformerAbstract
{
    /**
     * Category transformer.
     *
     * @param EvaluationToolSurveyStepResultAsset $surveLocalization
     * @return array
     */
    public function transform(EvaluationToolSurveyStepResultAsset $surveyStepResultAsset): array
    {
        return [
            "id"               => (int)$surveyStepResultAsset->id,
            "filename"             => (string)$surveyStepResultAsset->filename,
            "transcription"             => (string)$surveyStepResultAsset->transcription,
            "survey_step_result_id"             => (int)$surveyStepResultAsset->survey_step_result_id,
            "createdAt"        => $surveyStepResultAsset->created_at,
            "updatedAt"        => $surveyStepResultAsset->updated_at,
            "deletedAt"        => (string)$surveyStepResultAsset->deleted_at,
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
            "filename"        => "filename",
            "transcription"    => "transcription",
            "title"       => "title",
            "survey_step_result_id"     => "survey_step_result_id"
        ];
    }
}
