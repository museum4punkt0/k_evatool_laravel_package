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
            "survey_step_id"             => (int)$surveyStepResult->survey_step_id,
            "preseneted_at"        => (bool)$surveyStepResult->presented_at,
            "answered_at"        => (bool)$surveyStepResult->answered_at,
            "changed_answer"        => (int)$surveyStepResult->changed_answer,
            "session_id"        => (string)$surveyStepResult->session_id,
            "result_language_id"        => (int)$surveyStepResult->result_language_id,
            "result_value"        => $surveyStepResult->result_value,
            "is_skipped"        => $surveyStepResult->is_skipped,
            "time"        => $surveyStepResult->time,
            "params"        => $surveyStepResult->params,
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
            "id"          => "id",
            "survey_step_id"        => "survey_step_id",
            "presented_at"        => "presented_at",
            "answered_at"        => "answered_at",
            "changed_answer"        => "changed_answer",
            "session_id"        => "session_id",
            "result_language_id"        => "result_language_id",
            "result_value"        => "result_value",
            "is_skipped"        => "is_skipped",
            "time"        => "time",
            "params"        => "params",
        ];
    }
}
