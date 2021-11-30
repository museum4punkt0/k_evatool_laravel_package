<?php

namespace Twoavy\EvaluationTool\Transformers;

use CodeInc\HumanReadableFileSize\HumanReadableFileSize;
use League\Fractal\TransformerAbstract;
use Twoavy\EvaluationTool\Models\EvaluationToolAsset;

class EvaluationToolTranscriptionTransformer extends TransformerAbstract
{
    /**
     * Category transformer.
     *
     * @param EvaluationToolAsset $transcription
     * @return array
     */
    public function transform(EvaluationToolAsset $transcription): array
    {

        return [
            "id"                => (int)$transcription->id,
            "autoTranscription" => (string)$transcription->api_transcription,
            "transcription"     => (string)$transcription->manual_transcription,
            "createdAt"         => $transcription->created_at,
            "createdBy"         => $transcription->created_by_user ? $transcription->created_by_user->name : null,
            "updatedAt"         => $transcription->updated_at,
            "updatedBy"         => $transcription->updated_by_user ? $transcription->updated_by_user->name : null,
            "deletedAt"         => $transcription->deleted_at,
            "deletedBy"         => $transcription->deleted_by_user ? $transcription->deleted_by_user->name : null,
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
            "id"                => "id",
            "transcription"     => "manual_transcription",
            "autoTranscription" => "api_transcrption",
        ];
    }
}
