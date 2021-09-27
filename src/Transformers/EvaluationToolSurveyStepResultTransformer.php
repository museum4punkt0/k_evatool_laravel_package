<?php

namespace Twoavy\EvaluationTool\Transformers;

use League\Fractal\TransformerAbstract;
use Twoavy\EvaluationTool\Models\EvaluationToolSurveyStepResult;

class EvaluationToolSurveyStepResultTransformer extends TransformerAbstract
{
    /**
     * Category transformer.
     *
     * @param EvaluationToolSurveyStepResult $surveyStepResult
     * @return array
     */
    public function transform(EvaluationToolSurveyStepResult $surveyStepResult): array
    {
        return [
            "id"               => (int)$surveyStepResult->id,
            "surveyStepId"     => (int)$surveyStepResult->survey_step_id,
            "surveyId"         => (int)$surveyStepResult->survey_step->survey_id,
            "presentedAt"      => (string)$surveyStepResult->presented_at,
            "answeredAt"       => (string)$surveyStepResult->answered_at,
            "changedAnswer"    => (int)$surveyStepResult->changed_answer,
            "uuid"             => (string)$surveyStepResult->session_id,
            "resultLanguageId" => (int)$surveyStepResult->result_language_id,
            "resultValue"      => $surveyStepResult->result_value,
            "isSkipped"        => $surveyStepResult->is_skipped,
            "time"             => $surveyStepResult->time,
            "params"           => $surveyStepResult->params,
            "createdAt"        => $surveyStepResult->created_at,
            "updatedAt"        => $surveyStepResult->updated_at,
            "deletedAt"        => (string)$surveyStepResult->deleted_at,
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
            "id"               => "id",
            "surveyStepId"     => "survey_step_id",
            "presentedAt"      => "presented_at",
            "answeredAt"       => "answered_at",
            "changedAnswer"    => "changed_answer",
            "uuid"             => "session_id",
            "resultLanguageId" => "result_language_id",
            "resultValue"      => "result_value",
            "isSkipped"        => "is_skipped",
            "time"             => "time",
            "params"           => "params",
        ];
    }
}
