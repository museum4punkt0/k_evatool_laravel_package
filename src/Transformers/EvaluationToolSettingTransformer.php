<?php

namespace Twoavy\EvaluationTool\Transformers;

use Illuminate\Support\Facades\Storage;
use League\Fractal\TransformerAbstract;
use Twoavy\EvaluationTool\Models\EvaluationToolSetting;

class EvaluationToolSettingTransformer extends TransformerAbstract
{
    /**
     * Survey transformer.
     *
     * @param EvaluationToolSetting $settins
     * @return array
     */
    public function transform(EvaluationToolSetting $setting): array
    {
        $transformed = [
            "id"          => (int)$setting->id,
            "default"     => (boolean)$setting->default,
            "name"        => (string)$setting->name,
            "setting"     => $this->transformToUrl($setting->settings, $setting->settingsAssetUrlKeys),
            "surveyCount" => (int)$setting->surveys_count,
            "createdAt"   => $setting->created_at,
            "createdBy"   => $setting->created_by_user ? $setting->created_by_user->name : null,
            "updatedAt"   => $setting->updated_at,
            "updatedBy"   => $setting->updated_by_user ? $setting->updated_by_user->name : null,
            "deletedAt"   => (string)$setting->deleted_at,
            "deletedBy"   => $setting->deleted_by_user ? $setting->deleted_by_user->name : null,
            "links"       => [
                [
                    "rel"  => "self",
                    "href" => route("settings.show", $setting->id)
                ],
            ]
        ];

        return $transformed;
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
            "id"      => "id",
            "name"    => "name",
            "setting" => "settings",
            "default" => "default",
        ];
    }

    /**
     *  transform object values of given keys to url
     *
     * @param object $field
     * @param array $transformableKeys
     * @return object
     */
    protected function transformToUrl(object $field,  array $transformableKeys): object
    {
        foreach ($field as $key => $value) {
            if (in_array($key, $transformableKeys) && $value) {
                $field->{$key} = Storage::disk("evaluation_tool_settings_assets")->url($value);
            }
        }

        return $field;
    }

}
