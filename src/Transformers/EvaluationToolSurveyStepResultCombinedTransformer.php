<?php

namespace Twoavy\EvaluationTool\Transformers;

use League\Fractal\TransformerAbstract;
use Twoavy\EvaluationTool\Helpers\EvaluationToolHelper;
use Twoavy\EvaluationTool\Models\EvaluationToolAsset;
use Twoavy\EvaluationTool\Models\EvaluationToolSurveyElement;
use Twoavy\EvaluationTool\Models\EvaluationToolSurveyStep;

class EvaluationToolSurveyStepResultCombinedTransformer extends TransformerAbstract
{
    /**
     * Survey Step Result Combined transformer.
     *
     * @param EvaluationToolSurveyStep $surveyStep
     * @return array
     */
    public function transform(EvaluationToolSurveyStep $surveyStep): array
    {
        return [
            "id"                   => (int)$surveyStep->id,
            "uuid"                 => request()->uuid,
            "surveyElementType"    => (string)$surveyStep->survey_element->survey_element_type->key,
            "params"               => $this->loadAssets($surveyStep->survey_element->params, $surveyStep->survey_element->survey_element_type->key),
            "resultsCount"         => $surveyStep->survey_step_results_count,
            "demoResultsCount"     => $surveyStep->survey_step_demo_results_count,
            "resultByUuid"         => $this->getResultsByUuid($surveyStep),
            "sampleResultPayload"  => $surveyStep->sampleResultPayload,
            "timeBasedSteps"       => $surveyStep->timebased_steps_resolved,
            "nextStepId"           => $surveyStep->next_step_id ? (int)$surveyStep->next_step_id : null,
            //            "timeBasedSteps"         => (array)$surveyStep->time_based_steps,
            "resultBasedNextSteps" => $surveyStep->result_based_next_steps,
            "group"                => (string)$surveyStep->group,
            "allowSkip"            => (bool)$surveyStep->allow_skip,
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
            "id"                   => "id",
            "name"                 => "name",
            "surveyId"             => "survey_id",
            "surveyElementId"      => "survey_element_id",
            "nextStepId"           => "next_step_id",
            "timeBasedSteps"       => "time_based_steps",
            "resultBasedNextSteps" => "result_based_next_steps",
            "group"                => "group",
            "allowSkip"            => "allow_skip",
            "published"            => "published",
            "publishUp"            => "publish_up",
            "publishDown"          => "publish_down"
        ];
    }

    public function loadAssets($params, $type)
    {
        // Video
        if ($type == "video" && isset($params->videoAssetId)) {
            if ($videoAsset = EvaluationToolAsset::find($params->videoAssetId)) {
                $params->videoAsset = $videoAsset->only("id", "urls");
            }
        }

        // Yay nay
        if ($type == "yayNay" && isset($params->assetIds) && is_array($params->assetIds) && !empty($params->assetIds)) {

            $assets = [];

            foreach ($params->assetIds as $assetId) {
                if ($asset = EvaluationToolAsset::find($assetId)) {
                    $assets[] = $asset->only("id", "urls");
                }
            }

            $params->assets = $assets;

        }

        return $params;
    }

    public function getResultsByUuid(EvaluationToolSurveyStep $surveyStep)
    {
        if ($surveyStep->survey_element_type->key === "video") {
            return $surveyStep->survey_step_result_by_uuid ? $surveyStep->survey_step_result_by_uuid->map(function ($result) {
                return [
                    "timecode"    => $result->time,
                    "resultValue" => $result->result_value
                ];
            }) : null;
        } else {
            return $surveyStep->survey_step_result_by_uuid ? $surveyStep->survey_step_result_by_uuid->pluck("result_value")->first() : null;
        }
    }
}
