<?php

namespace Twoavy\EvaluationTool\Transformers;

use League\Fractal\TransformerAbstract;
use Twoavy\EvaluationTool\Models\EvaluationToolSurvey;

class EvaluationToolSurveyTransformer extends TransformerAbstract
{
    /**
     * Survey transformer.
     *
     * @param EvaluationToolSurvey $survey
     * @return array
     */
    public function transform(EvaluationToolSurvey $survey): array
    {
        $transformed = [
            "id"                     => (int)$survey->id,
            "name"                   => (string)$survey->name,
            "slug"                   => (string)$survey->slug,
            "description"            => (string)$survey->description,
            "published"              => (bool)$survey->published,
            "publishUp"              => $survey->publish_up,
            "publishDown"            => $survey->publish_down,
            "languages"              => $survey->languages->pluck("code"),
            "surveyStepsCount"       => $survey->survey_steps_count,
            "surveyResultsCount"     => $survey->survey_results_count,
            "surveyDemoResultsCount" => $survey->survey_demo_results_count,
            "hasResults"             => (boolean)$survey->has_results,
            "statusByUuid"           => $survey->status ?: null,
            "adminLayout"            => $survey->admin_layout ?: [],
            "settingsId"            => $survey->settings_id ?: null,
            "createdAt"              => $survey->created_at,
            "createdBy"              => $survey->created_by_user ? $survey->created_by_user->name : null,
            "updatedAt"              => $survey->updated_at,
            "updatedBy"              => $survey->updated_by_user ? $survey->updated_by_user->name : null,
            "deletedAt"              => (string)$survey->deleted_at,
            "deletedBy"              => $survey->deleted_by_user ? $survey->deleted_by_user->name : null,
            "links"                  => [
                [
                    "rel"  => "self",
                    "href" => route("surveys.show", $survey->id)
                ],
                [
                    "rel"  => "survey-steps",
                    "href" => route("surveys.steps.index", $survey->id)
                ],
            ]
        ];

        if (request()->has("is_run")) {
            unset($transformed["adminLayout"]);
        }

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
            "id"          => "id",
            "name"        => "name",
            "slug"        => "slug",
            "description" => "description",
            "published"   => "published",
            "publishUp"   => "publish_up",
            "publishDown" => "publish_down",
            "adminLayout" => "admin_layout",
            "languages"   => "languages",
            "settingsId"  => "settings_id"
        ];
    }
}
