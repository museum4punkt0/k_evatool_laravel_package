<?php

namespace Twoavy\EvaluationTool\Transformers;

use CodeInc\HumanReadableFileSize\HumanReadableFileSize;
use League\Fractal\TransformerAbstract;
use Twoavy\EvaluationTool\Models\EvaluationToolAsset;

class EvaluationToolAssetTransformer extends TransformerAbstract
{
    /**
     * Category transformer.
     *
     * @param EvaluationToolAsset $surveyAsset
     * @return array
     */
    public function transform(EvaluationToolAsset $surveyAsset): array
    {

        return [
            "id"             => (int)$surveyAsset->id,
            "filename"       => (string)$surveyAsset->filename,
            "hash"           => (string)$surveyAsset->hash,
            "mime"           => (string)$surveyAsset->mime,
            "size"           => (int)$surveyAsset->size,
            "sizeHuman"      => HumanReadableFileSize::getHumanSize($surveyAsset->size),
            "urls"           => (array)$surveyAsset->urls,
            "meta"           => $surveyAsset->meta,
            "surveyElements" => (int)$surveyAsset->survey_elements->count(),
            "createdAt"      => $surveyAsset->created_at,
            "createdBy"      => $surveyAsset->created_by_user ? $surveyAsset->created_by_user->name : null,
            "updatedAt"      => $surveyAsset->updated_at,
            "updatedBy"      => $surveyAsset->updated_by_user ? $surveyAsset->updated_by_user->name : null,
            "deletedAt"      => $surveyAsset->deleted_at,
            "deletedBy"      => $surveyAsset->deleted_by_user ? $surveyAsset->deleted_by_user->name : null,
            "links"          => [
                [
                    "rel"  => "self",
                    "href" => route("assets.show", $surveyAsset->id)
                ]
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
            "id"       => "id",
            "filename" => "filename",
            "hash"     => "hash",
        ];
    }
}
