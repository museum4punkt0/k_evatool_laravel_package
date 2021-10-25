<?php

namespace Twoavy\EvaluationTool\Helpers;

use Illuminate\Http\Request;
use Twoavy\EvaluationTool\Models\EvaluationToolSurvey;
use Twoavy\EvaluationTool\Models\EvaluationToolSurveyLanguage;
use Twoavy\EvaluationTool\Models\EvaluationToolSurveyStep;

class EvaluationToolHelper
{
    public static function transformModel($model, $removeDataKey = true): ?array
    {
        if (isset($model->transformer)) {
            $transformer    = $model->transformer;
            $transformation = fractal($model, new $transformer);
            if ($removeDataKey) {
                return $transformation->toArray()["data"];
            }
            return $transformation->toArray();
        }
        return $model;
    }

    public static function transformData($data, $transformer, $removeDataKey = true)
    {
        $transformation = fractal($data, new $transformer);
        if ($removeDataKey) {
            $data = $transformation->toArray();
            return $data["data"];
        }
        return $transformation->toArray();
    }

    public static function reverseTransform(Request $request, $transformer)
    {
        $transformedInput = [];

        foreach ($request->request->all() as $input => $value) {
            if ($transformer::originalAttribute($input)) {
                $transformedInput[$transformer::originalAttribute($input)] = $value;
            }
        }

        $request->replace($transformedInput);
    }

    public static function getPrimaryLanguage()
    {
        return EvaluationToolSurveyLanguage::where("default", true)->first();
    }

    public static function getSecondaryLanguages()
    {
        return EvaluationToolSurveyLanguage::where("default", false)->get();
    }

    /**
     * @param EvaluationToolSurvey $survey
     * @param $excludeSurveyStepId
     * @return array
     */
    public static function getUsedSteps(EvaluationToolSurvey $survey, $excludeSurveyStepId = null): array
    {
        $surveyStepsQuery = EvaluationToolSurveyStep::where("survey_id", $survey->id);
        if ($excludeSurveyStepId) {
            $surveyStepsQuery->where("id", "!=", $excludeSurveyStepId);
        }
        $surveySteps = $surveyStepsQuery->get();

        $usedStepIds = [
            "next"        => [],
            "timeBased"   => [],
            "resultBased" => []
        ];
        foreach ($surveySteps as $surveyStep) {
            // check for next step
            if ($surveyStep->next_step_id) {
                $usedStepIds["next"][] = $surveyStep->next_step_id;
            }

            // check for time based steps
            if ($surveyStep->time_based_steps && is_array($surveyStep->time_based_steps) && !empty($surveyStep->time_based_steps)) {
                foreach ($surveyStep->time_based_steps as $timeBasedStep) {
                    $usedStepIds["timeBased"][] = $timeBasedStep->stepId;
                }
            }

            // check for result based next step
            if ($surveyStep->result_based_next_steps && is_array($surveyStep->result_based_next_steps) && !empty($surveyStep->result_based_next_steps)) {
                foreach ($surveyStep->result_based_next_steps as $resultBasedNextStep) {
                    $usedStepIds["resultBased"][] = $resultBasedNextStep->stepId;
                }
            }
        }

        return $usedStepIds;
    }
}
