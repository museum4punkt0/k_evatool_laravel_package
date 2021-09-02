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
     * @param EvaluationToolAsset $asset
     * @return array
     */
    public function transform(EvaluationToolAsset $asset): array
    {

        return [
            "id"        => (int)$asset->id,
            "filename"  => (string)$asset->filename,
            "hash"      => (string)$asset->hash,
            "size"      => (int)$asset->size,
            "sizeHuman" => HumanReadableFileSize::getHumanSize($asset->size),
            "createdAt" => $asset->created_at,
            "updatedAt" => $asset->updated_at,
            "deletedAt" => (string)$asset->deleted_at,
            "links"     => [
                [
                    "rel"  => "self",
                    "href" => route("assets.show", $asset->id)
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
