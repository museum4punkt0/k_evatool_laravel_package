<?php

namespace Twoavy\EvaluationTool\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Twoavy\EvaluationTool\Models\EvaluationToolSurvey;
use Twoavy\EvaluationTool\Models\EvaluationToolSurveyStep;
use Twoavy\EvaluationTool\Traits\EvaluationToolResponse;

class EvaluationToolSurveySeedController extends Controller
{
    use EvaluationToolResponse;

    public function __construct()
    {
        $this->middleware("auth:api");
        $this->timestamp = Carbon::now()->subMinutes(rand(5, 60 * 24 * 30 * 6));
        $this->uuid      = Str::uuid();

        $this->surveyRunController = new EvaluationToolSurveySurveyRunController();
    }

    /**
     *  Seed a single survey
     *
     * @param EvaluationToolSurvey $survey
     * @param int $count
     */
    public function seedResults(EvaluationToolSurvey $survey, int $count = 500)
    {
        $seedCount = 0;

        if (request()->has("count") && is_int(request()->count)) {
            $seedCount = request()->count;
        }

        while ($seedCount < $count) {

            $this->uuid = Str::uuid();
            $languageId = $survey->languages->random(1)->first()->id;

            $this->timestamp = Carbon::now()->subMinutes(rand(5, 60 * 24 * 30 * 18));

            $surveySteps = $survey->survey_steps->filter(function ($value) {
                return is_null($value->parent_step_id);
            });

            $surveySteps = $this->getStepsWithResults($surveySteps);

            $position = $this->surveyRunController->getPositionWithinSurvey($surveySteps);

            while ($position["currentStep"] != -1) {
                $step = EvaluationToolSurveyStep::find($position["currentStep"]);
                echo "current step: " . $position["currentStep"] . " - " . $step->survey_element->survey_element_type->key . PHP_EOL;

                $success = $this->seedSurveyStepResult($step, $languageId);
                if (!$success) {
                    echo "seed method not found" . PHP_EOL . PHP_EOL;
                    break;
                }
                $surveySteps = $this->getStepsWithResults($surveySteps);
                $position    = $this->surveyRunController->getPositionWithinSurvey($surveySteps);
//                print_r($position);
            }

            echo "seed round " . ($seedCount + 1) . " done" . PHP_EOL;

            $seedCount++;
        }
    }

    public function seedSurveyStepResult(EvaluationToolSurveyStep $surveyStep, $languageId): bool
    {
        $elementType = $surveyStep->survey_element->survey_element_type->key;

        $className = 'Twoavy\EvaluationTool\SurveyElementTypes\EvaluationToolSurveyElementType' . ucfirst($elementType);
        if (class_exists($className)) {
            if (method_exists($className, "seedResult")) {
                $this->timestamp->addSeconds(rand(5, 60));
                $className::seedResult($surveyStep, $this->uuid, $languageId, $this->timestamp);
                return true;
            }
            return false;
        }
        return false;
    }

    public function getStepsWithResults($surveySteps)
    {
        foreach ($surveySteps as $surveyStep) {
            $resultsByUuid            = $this->surveyRunController->getResultsByUuid($surveyStep, $this->uuid);
            $surveyStep->resultByUuid = $resultsByUuid->result;
            $surveyStep->isAnswered   = $resultsByUuid->isAnswered;
        }
        return $surveySteps;
    }
}
