<?php

namespace Twoavy\EvaluationTool\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Twoavy\EvaluationTool\Helpers\EvaluationToolHelper;
use Twoavy\EvaluationTool\Http\Requests\EvaluationToolSurveyStepStoreRequest;
use Twoavy\EvaluationTool\Models\EvaluationToolSurvey;
use Twoavy\EvaluationTool\Models\EvaluationToolSurveyStep;
use Twoavy\EvaluationTool\Traits\EvaluationToolResponse;

class EvaluationToolSurveySurveyStepController extends Controller
{
    use EvaluationToolResponse;

    public function __construct()
    {
        $this->middleware("auth:api");
    }

    /**
     * @param EvaluationToolSurvey $survey
     * @return JsonResponse
     */
    public function index(EvaluationToolSurvey $survey): JsonResponse
    {
        $ordering    = EvaluationToolHelper::sortSurveySteps($survey);
        $surveySteps = $survey->survey_steps->sortBy(function ($model) use ($ordering) {
            return array_search($model->getKey(), $ordering->toArray());
        });
        return $this->showAll($surveySteps);
    }

    /**
     * @param EvaluationToolSurvey $survey
     * @param EvaluationToolSurveyStep $step
     * @return JsonResponse
     */
    public function show(EvaluationToolSurvey $survey, EvaluationToolSurveyStep $step): JsonResponse
    {
        if ($step->survey_id !== $survey->id) {
            return $this->errorResponse("step does not belong to survey", 409);
        }
        return $this->showOne($step);
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
     * @param EvaluationToolSurveyStep $step
     * @param EvaluationToolSurveyStepStoreRequest $request
     * @return JsonResponse
     */
    public function update(EvaluationToolSurvey $survey, EvaluationToolSurveyStep $step, EvaluationToolSurveyStepStoreRequest $request): JsonResponse
    {
        // check if survey id and step id match
        if ($step->survey_id !== $survey->id) {
            return $this->errorResponse("survey id does not match step id", 409);
        }

        // disallow change if results for step exist (including demo results)
        if ($step->survey_element_id !== $request->survey_element_id && ($step->survey_step_results_count > 0 || $step->survey_step_demo_results_count > 0)) {
            return $this->errorResponse("element cannot be changed because step has valid results", 409);
        }

        $step->fill($request->all());
        $step->save();

        $survey->updated_at = Carbon::now();
        $survey->save();

        return $this->showOne($step);
    }

    public function destroy(EvaluationToolSurvey $survey, EvaluationToolSurveyStep $step): JsonResponse
    {
        // check if survey id and step id match
        if ($step->survey_id !== $survey->id) {
            return $this->errorResponse("survey id does not match step id", 409);
        }
        $step->delete();
        return $this->showOne($step);
    }

    /**
     * @param EvaluationToolSurvey $survey
     * @param EvaluationToolSurveyStep $step
     * @param EvaluationToolSurveyStepStoreRequest $request
     * @return JsonResponse
     */
    public function setNextStep(EvaluationToolSurvey $survey, EvaluationToolSurveyStep $step, Request $request):
    JsonResponse
    {
        // check if survey id and step id match
        if ($step->survey_id !== $survey->id) {
            return $this->errorResponse("survey id does not match step id", 409);
        }

        $nextStep = EvaluationToolSurveyStep::find($request->nextStepId);
        if ($nextStep->survey_id !== $survey->id) {
            return $this->errorResponse("survey id does not match next step id", 409);
        }

        if ($step->id === $request->nextStepId) {
            return $this->errorResponse("step ids are equal. must be different", 409);
        }

        $step->next_step_id = $request->nextStepId;
        $step->save();
        return $this->showOne($step);
    }

    /**
     * @param EvaluationToolSurvey $survey
     * @param EvaluationToolSurveyStep $step
     * @return JsonResponse
     */
    public function setStartStep(EvaluationToolSurvey $survey, EvaluationToolSurveyStep $step):
    JsonResponse
    {
        // check if survey id and step id match
        if ($step->survey_id !== $survey->id) {
            return $this->errorResponse("survey id does not match step id", 409);
        }

        if ($currentFirstStep = EvaluationToolSurveyStep::where("survey_id", $step->survey_id)->whereNotNull("is_first_step")->first()) {
            $currentFirstStep->is_first_step = null;
            $currentFirstStep->save();
        }

        $step->is_first_step = true;
        $step->save();
        return $this->showOne($step);
    }

    /**
     * @param EvaluationToolSurvey $survey
     * @param EvaluationToolSurveyStep $step
     * @return JsonResponse
     */
    public function removeNextStep(EvaluationToolSurvey $survey, EvaluationToolSurveyStep $step):
    JsonResponse
    {
        // check if survey id and step id match
        if ($step->survey_id !== $survey->id) {
            return $this->errorResponse("survey id does not match step id", 409);
        }

        $step->next_step_id = null;
        $step->save();
        return $this->showOne($step);
    }
}
