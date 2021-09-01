<?php

namespace Twoavy\EvaluationTool\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Twoavy\EvaluationTool\Http\Requests\EvaluationToolSurveyStepStoreRequest;
use Twoavy\EvaluationTool\Models\EvaluationToolSurvey;
use Twoavy\EvaluationTool\Models\EvaluationToolSurveyStep;
use Twoavy\EvaluationTool\Traits\EvaluationToolResponse;

class EvaluationToolSurveySurveyStepController extends Controller
{
    use EvaluationToolResponse;

    /**
     * @param EvaluationToolSurvey $survey
     * @return JsonResponse
     */
    public function index(EvaluationToolSurvey $survey): JsonResponse
    {
        $surveySteps = $survey->survey_steps;
        return $this->showAll($surveySteps);
    }

    /**
     * @param EvaluationToolSurvey $survey
     * @param EvaluationToolSurveyStepStoreRequest $request
     * @return JsonResponse
     */
    public function store(EvaluationToolSurvey $survey, EvaluationToolSurveyStepStoreRequest $request): JsonResponse
    {

        $surveyStep            = new EvaluationToolSurveyStep();
        $surveyStep->survey_id = $survey->id;
        $surveyStep->fill($request->all());
        $surveyStep->save();
        return $this->showOne($surveyStep);
    }

    /**
     * @param EvaluationToolSurvey $survey
     * @param EvaluationToolSurveyStepStoreRequest $request
     * @return JsonResponse
     */
    public function update(EvaluationToolSurvey $survey, EvaluationToolSurveyStepStoreRequest $request): JsonResponse
    {
        return $this->showOne($survey);
    }
}
