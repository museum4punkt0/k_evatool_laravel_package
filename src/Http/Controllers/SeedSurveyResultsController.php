<?php

namespace Twoavy\EvaluationTool\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use StdClass;
use Twoavy\EvaluationTool\Http\Requests\EvaluationToolSurveyStepResultAssetStoreRequest;
use Twoavy\EvaluationTool\Models\EvaluationToolSurvey;
use Twoavy\EvaluationTool\Models\EvaluationToolSurveyLanguage;
use Twoavy\EvaluationTool\Models\EvaluationToolSurveyStep;
use Twoavy\EvaluationTool\Models\EvaluationToolSurveyStepResult;
use Twoavy\EvaluationTool\Models\EvaluationToolSurveyStepResultAsset;
use Twoavy\EvaluationTool\SurveyElementTypes\EvaluationToolSurveyElementTypeStarRating;
use Twoavy\EvaluationTool\Traits\EvaluationToolResponse;
use Twoavy\EvaluationTool\Transformers\EvaluationToolSurveyStepResultCombinedTransformer;
use Twoavy\EvaluationTool\Http\Requests\EvaluationToolSurveySurveyStepResultStoreRequest;
use Twoavy\EvaluationTool\Transformers\EvaluationToolSurveyTransformer;
use Twoavy\EvaluationTool\Http\Controllers\EvaluationToolSurveySurveyRunController;

class EvaluationToolSeedSurveyResultsController extends Controller
{
    public function iterate($surveyId){
        // get survey
        if (!$survey = EvaluationToolSurvey::find($surveyId)){
            return $this->errorResponse("survey not found", 409);
        }
        // get steps
        $surveySteps = $survey->survey_steps->filter(function ($value) {
            return is_null($value->parent_step_id);
        });

        $surveyRunController = new EvaluationToolSurveySurveyRunController();
        $position = $surveyRunController->getPositionWithinSurvey($surveySteps);
        dd($position);

        // get start element
        // create timestamp
        // create result
        // seedSurveyStepResult();
        // get next step based on result

    }
    public function seedSurveyStepResult(EvaluationToolSurveyStep $surveyStep, $uuid, $language, $timestamp): StdClass
    {
        $payload              = new StdClass;
        $payload->elementType = $surveyStep->survey_element->survey_element_type->key;

        $samplePayloadFunctionName           = 'samplePayload' . ucfirst($payload->elementType);
        $payload->resultData                 = new StdClass;
        $payload->resultData->resultValue    = $this->{$samplePayloadFunctionName}($surveyStep->survey_element->params);
        $payload->resultData->resultLanguage = $this->defaultLanguage->code;

        return $payload;
    }
}
