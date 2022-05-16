<?php

namespace Twoavy\EvaluationTool\Utilities;

use StdClass;
use Twoavy\EvaluationTool\Http\Controllers\EvaluationToolSurveySurveyRunController;
use Twoavy\EvaluationTool\Models\EvaluationToolSurvey;
use Twoavy\EvaluationTool\Models\EvaluationToolSurveyStepResult;
use Twoavy\EvaluationTool\SurveyElementTypes\EvaluationToolSurveyElementTypeBinary;
use Twoavy\EvaluationTool\SurveyElementTypes\EvaluationToolSurveyElementTypeEmoji;
use Twoavy\EvaluationTool\SurveyElementTypes\EvaluationToolSurveyElementTypeMultipleChoice;
use Twoavy\EvaluationTool\SurveyElementTypes\EvaluationToolSurveyElementTypeStarRating;

class EvaluationToolSurveyProgress
{
    public function __construct($uuid)
    {
        $this->uuid = $uuid;

        $this->doneCount = 0;
        // Helper to determine survey's current step inside path
        $this->currentStepReminder = null;

        $this->remainingPath = null;
        $this->remainingCount = 1;
    }

    public function surveyPath($surveySlug)
    {
        if (!$survey = EvaluationToolSurvey::where("slug", $surveySlug)->first()) {
            return $this->errorResponse("survey not found", 409);
        }

        $results = null;

        if ($this->uuid) {
            // get time based steps to remove those from the results list
            // they give no information about next step (not relevant for survey progress detection tree)
            $timeBasedStepIds = $survey
                ->survey_steps()
                ->whereNotNull('time_based_steps')
                ->pluck('time_based_steps')
                ->flatten()
                ->pluck('stepId');

            $results = EvaluationToolSurveyStepResult::where("session_id", $this->uuid)
                ->whereIn("survey_step_id", $survey->survey_steps->pluck("id"))
                ->whereNotIn("survey_step_id", $timeBasedStepIds)
                ->orderBy("answered_at", "ASC")->get();

        }

        $path         = new StdClass;
        $firstStep    = $survey->survey_steps->where("is_first_step")->first();
        $path->stepId = $firstStep->id;

        // check of step has results and label as "done"
        $done      = false;
        $remaining = false;

        if ($results->isNotEmpty() && $results->first()->survey_step_id == $firstStep->id) {
            $path->done = true;
            $done       = true;
            $this->doneCount++;

            if ($results->count() == 1) {
                $lastResult = $results->first();
                $this->setCurrentStepReminder($lastResult);
            }

            // remove the first result
            $results->shift();
            if ($results->count() === 0) {
                $path->lastDone = true;
                $remaining      = true;
                $path->ended    = $this->checkLastResultForEnd($lastResult, $firstStep);
            }
        }

        $path->children = $this->followPath($firstStep->id, $survey, $results, $done, $remaining);

        $remainingCount = $this->getRemainingCount($path, $this->doneCount);
        $maxCount = $this->doneCount + $remainingCount;

        $response                    = new StdClass;
        $response->doneCount         = $this->doneCount;
        $response->remainingCount    = $remainingCount;
        $response->maxCount          = $maxCount;
        $response->currentStepNumber = $remainingCount === 0 ? $maxCount : $this->doneCount + 1;
        $response->path              = $path;

        return $response;
    }

    public function followPath($stepId, $survey, $results, $stepIsDone = false, $remaining = false): array
    {
        $step        = $survey->survey_steps->find($stepId);
        $element     = $step->survey_element;
        $elementType = $element->survey_element_type->key;

        $pathParts = [];

        if ($step->next_step_id) {
            $pathParts[] = $survey->survey_steps->find($step->next_step_id)->id;
        }

        if ($step->result_based_next_steps) {

            // the same array merge works on all element types. yet they are split here in case they need to be handled individually
            if ($elementType == "emoji") {
                $pathParts = array_merge($pathParts, collect($step->result_based_next_steps)->pluck("stepId")->toArray());
            }
            if ($elementType == "multipleChoice") {
                $pathParts = array_merge($pathParts, collect($step->result_based_next_steps)->pluck("stepId")->toArray());
            }
            if ($elementType == "binary") {
                $pathParts = array_merge($pathParts, collect($step->result_based_next_steps)->pluck("stepId")->toArray());
            }
            if ($elementType == "starRating") {
                $pathParts = array_merge($pathParts, collect($step->result_based_next_steps)->pluck("stepId")->toArray());
            }
        }

        // array of elements that shall be amended to the path
        $pathAmend = [];

        if (!empty($pathParts)) {
            foreach ($pathParts as $pathPart) {
                $subPath         = new StdClass;
                $subPath->stepId = $pathPart;

                // check if there are (still) results and previous step is done
                $done = $stepIsDone;
                if ($results->count() > 0 && $stepIsDone) {
                    // check of step has results and label as "done"
                    if ($results->first()->survey_step_id == $pathPart) {
                        $subPath->done = true;
                        $done          = true;
                        $this->doneCount++;

                        if ($results->count() == 1) {
                            $lastResult = $results->first();
                            $lastStep = $this->setCurrentStepReminder($lastResult);
                        }

                        $results->shift();

                        if ($results->count() == 0) {
                            $subPath->lastDone = true;
                            $remaining         = true;
                            $subPath->ended    = $this->checkLastResultForEnd($lastResult, $lastStep);
                            $subPath->nextStep = $this->currentStepReminder;
                        }
                    }
                }

                if ($this->currentStepReminder === $subPath->stepId) {
                    $subPath->isCurrent = true;
                    $this->currentStepReminder = null;
                }

                // keep following the path recursively
                if ($children = $this->followPath($pathPart, $survey, $results, $done, $remaining)) {
                    $subPath->children = $children;
                }

                $pathAmend[] = $subPath;
            }
        }

        return $pathAmend;
    }

    protected function checkLastResultForEnd($result, $step): bool
    {
        $elementType = $step->survey_element_type->key;

        if (!$step->next_step_id) {
            if (!$step->result_based_next_steps) {
                return true;
            } else {
                if ($elementType == "emoji") {
                    return EvaluationToolSurveyElementTypeEmoji::isResultBasedMatch($result, $step);
                }

                if ($elementType == "binary") {
                    return EvaluationToolSurveyElementTypeBinary::isResultBasedMatch($result, $step);
                }

                if ($elementType == "starRating") {
                    return EvaluationToolSurveyElementTypeStarRating::isResultBasedMatch($result, $step);
                }

                if ($elementType == "multipleChoice") {
                    return EvaluationToolSurveyElementTypeMultipleChoice::isResultBasedMatch($result, $step);
                }
            }
        }
        return false;
    }

    /**
     *  Get the survey path depth
     *
     * @param $path
     * @param int $depth
     */
    protected function setRemainingCount($path, int $depth = 1)
    {
        if ($this->remainingCount< $depth) {
            $this->remainingCount= $depth;
        }

        if (isset($path->children)) {
            foreach ($path->children as $subPath) {
                $this->setRemainingCount($subPath, $depth + 1);
            }
        }
    }

    /**
     *  Set remaining path from current step
     *
     * @param $path
     */
    protected function setRemainingPath($path)
    {
        if (isset($path->isCurrent)) {
            $this->remainingPath = $path;
        }

        if (isset($path->children)) {
            foreach($path->children as $subPath) {
                $this->setRemainingPath($subPath);
            }
        }
    }

    /**
     * @param $path
     * @return int
     */
    protected function getRemainingCount($path, $doneCount) :int
    {
        if ($doneCount === 0) $this->remainingPath = $path;

        $this->setRemainingPath($path);

        $this->setRemainingCount($this->remainingPath);

        return $this->remainingCount;
    }

    /**
     *  Sets currentStepReminder helper to determine survey's current step inside path
     *
     * @param $lastResult
     * @return mixed
     */
    protected function setCurrentStepReminder($lastResult)
    {
        $lastStep = $lastResult->survey_step;

        if (isset($lastStep->result_based_next_steps)) {
            $lastStep->resultByUuid = (new EvaluationToolSurveySurveyRunController)->getResultsByUuid($lastStep, $this->uuid)->result;
            $this->currentStepReminder =  (new EvaluationToolSurveySurveyRunController)->getResultBasedNextStep($lastStep)->id;
        } else {
            $this->currentStepReminder = $lastStep->next_step_id;
        }

        return $lastStep;
    }

}
