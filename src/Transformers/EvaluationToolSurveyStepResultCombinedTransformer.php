<?php

namespace Twoavy\EvaluationTool\Transformers;

use League\Fractal\TransformerAbstract;
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
//            "params"               => $surveyStep->survey_element->params,
            "params"               => $this->loadAssets($surveyStep->survey_element->params, $surveyStep->survey_element->survey_element_type->key),
            "results"              => $surveyStep->survey_step_results,
            "resultsByUuid"        => $surveyStep->survey_step_results_by_uuid,
            "sampleResultPayload"  => $surveyStep->sampleResultPayload,
            //            "name"                 => (string)$surveyStep->name,
            //            "surveyId"             => (int)$surveyStep->survey_id,
            //            "surveyElementId"      => (int)$surveyStep->survey_element_id,
            "nextStepId"           => $surveyStep->next_step_id ? (int)$surveyStep->next_step_id : null,
            "timeBasedSteps"       => (array)$surveyStep->time_based_steps,
            "resultBasedNextSteps" => $surveyStep->result_based_next_steps,
            /*"published"            => (bool)$surveyStep->published,
            "publishUp"            => $surveyStep->publish_up,
            "publishDown"          => $surveyStep->publish_down,*/
            "group"                => (string)$surveyStep->group,
            "allowSkip"            => (bool)$surveyStep->allow_skip,
            /*"links"                => [
                [
                    "rel"  => "self",
                    "href" => route("surveys.survey-steps.show", [$surveyStep->survey_id, $surveyStep->id])
                ],
                [
                    "rel"  => "survey",
                    "href" => route("surveys.show", $surveyStep->survey_id)
                ],
                [
                    "rel"  => "surveyElement",
                    "href" => route("survey-elements.show", $surveyStep->survey_element_id)
                ],
            ]*/
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
            "publishDown"          => "publish_down",
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
}
