<?php

namespace Twoavy\EvaluationTool\Helpers;

use Illuminate\Http\Request;
use Twoavy\EvaluationTool\Models\EvaluationToolSurvey;
use Twoavy\EvaluationTool\Models\EvaluationToolSurveyElement;
use Twoavy\EvaluationTool\Models\EvaluationToolSurveyLanguage;
use Twoavy\EvaluationTool\Models\EvaluationToolSurveyStep;

class EvaluationToolHelper
{
    public static function transformModel($model, $removeDataKey = true, $transformerOverride = false): ?array
    {
        if (isset($model->transformer)) {
            $transformer = $model->transformer;

            if ($transformerOverride) {
                $transformer = $transformerOverride;
            }

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

    public static function checkMissingLanguages(EvaluationToolSurveyElement $element, $keysToCheck = []): array
    {

        $missing = [];

        // iterate all keys
        foreach ($keysToCheck as $keyToCheck) {
            // iterate all surveys
            // check for array
            if (strpos($keyToCheck, ".*.") !== false) {
                list($key1, $key2) = explode(".*.", $keyToCheck);
                foreach ($element->params->{$key1} as $i => $subElement) {
                    if (isset($subElement->{$key2})) {
                        $missing[$key1 . "." . $i . "." . $key2] = self::getMissingLanguages($element, $subElement->{$key2}, $keyToCheck);
                    }
                }
            } // check for sub key
            elseif (strpos($keyToCheck, ".") !== false) {
                list($key1, $key2) = explode(".", $keyToCheck);
                if (isset($element->params->{$key1}->{$key2})) {
                    $missing[$key1 . "." . $key2] = self::getMissingLanguages($element, $element->params->{$key1}->{$key2}, $keyToCheck);
                }
            } else {
                if (isset($element->params->{$keyToCheck})) {
                    $missing[$keyToCheck] = self::getMissingLanguages($element, $element->params->{$keyToCheck}, $keyToCheck);
                }
            }
        }

        return $missing;
    }

    public static function getMissingLanguages($element, $lookIn, $keyToCheck)
    {
        return $element->surveys->map(function ($survey) use ($element, $keyToCheck, $lookIn) {

            $languageCodes = $survey->languages;

            $missingIn = [
                "surveyId" => $survey->id,
                //                "codes"    => $languageCodes->pluck("code")->flatten()
            ];

            $index = 0;

            foreach ($lookIn as $key => $text) {
                // check if code exists in collection and only return when no match
                $languageCodes = $languageCodes->filter(function ($value) use ($key) {
                    return $value->code !== $key;
                });
                $index++;
            }
            $missingIn["codes"] = $languageCodes->pluck("code")->flatten();

            // return null if array im empty
            if (empty($missingIn["codes"]->toArray())) {
                return null;
            }

            return $missingIn;

        })->filter()->values();
    }

    public static function checkCompleteLanguages($request, $keysToCheck)
    {
        $languageCodes = EvaluationToolSurveyLanguage::all()->pluck("code");

        $fullCount      = 0;
        $languagesCount = [];
        foreach ($languageCodes as $languageCode) {
            $languagesCount[$languageCode] = 0;
        }

        foreach ($keysToCheck as $key) {
            if (strpos($key, ".*.") !== false) {
                list($key1, $key2) = explode(".*.", $key);
                if (isset($request->params[$key1])) {
                    $fullCount += count($request->params[$key1]);
                    foreach ($request->params[$key1] as $keyParam) {
                        if (isset($keyParam[$key2])) {
                            foreach ($keyParam[$key2] as $languageCode => $value) {
                                if (!in_array($languageCode, $languageCodes->toArray())) {
                                    abort(422, "invalid language code (" . $languageCode . ")");
                                }
                                $languagesCount[$languageCode]++;
                            }
                        }
                    }
                }
            } elseif (strpos($key, ".") !== false) {
                list($key1, $key2) = explode(".", $key);
            } else {
                if (isset($request->params[$key])) {
                    foreach ($request->params[$key] as $languageCode => $value) {
                        if (!in_array($languageCode, $languageCodes->toArray())) {
                            abort(422, "invalid language code (" . $languageCode . ")");
                        }
                        $languagesCount[$languageCode]++;
                    }
                }
                $fullCount++;
            }
        }

        $maxCount = 0;
        foreach ($languagesCount as $languageCount) {
            if ($maxCount < $languageCount)
                $maxCount = $languageCount;
        }

        if ($maxCount < $fullCount) {
            abort(422, "at least one language must be provided with all keys");
        }
    }
}
