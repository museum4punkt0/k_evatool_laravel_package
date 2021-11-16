<?php

namespace Twoavy\EvaluationTool\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Twoavy\EvaluationTool\Http\Controllers\EvaluationToolSurveySurveyRunController;
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
        $this->uuid = Str::uuid();
    }

    /**
     *  Seed a single survey
     *
     * @param EvaluationToolSurvey $survey
     * @return JsonResponse
     */
    public function seedResults(EvaluationToolSurvey $survey)
    {
        $languageCode = $survey->languages->random(1)->first()->code;

        $surveySteps = $survey->survey_steps->filter(function ($value) {
            return is_null($value->parent_step_id);
        });

        $surveyRunController = new EvaluationToolSurveySurveyRunController();
        $position = $surveyRunController->getPositionWithinSurvey($surveySteps);

        $counter = 0;

        while ($position["currentStep"] != -1) {
            $success = $this->seedSurveyStepResult(EvaluationToolSurveyStep::find($position["currentStep"]), $languageCode);
            if (!$success) {
                echo "seed method not found";
                break;
            }
            $position = $surveyRunController->getPositionWithinSurvey($surveySteps);
            print_r($counter++);
        }
    }
    public function seedSurveyStepResult(EvaluationToolSurveyStep $surveyStep, $languageCode): bool
    {
        $elementType = $surveyStep->survey_element->survey_element_type->key;
        $className = 'Twoavy\EvaluationTool\SurveyElementTypes\EvaluationToolSurveyElementType' . ucfirst($elementType);
        if (class_exists($className)) {
            if (method_exists($className, "seedResult")) {
                $className::seedResult($surveyStep->survey_element(), $this->uuid, $languageCode);
                return true;
            }
            return false;
        }
    }
}
