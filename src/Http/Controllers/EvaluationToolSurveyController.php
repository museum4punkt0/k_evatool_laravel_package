<?php

namespace Twoavy\EvaluationTool\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Twoavy\EvaluationTool\Http\Requests\EvaluationToolSurveyStoreRequest;
use Twoavy\EvaluationTool\Models\EvaluationToolSurvey;
use Twoavy\EvaluationTool\Traits\EvaluationToolResponse;

class EvaluationToolSurveyController extends Controller
{
    use EvaluationToolResponse;

    /**
     * Retrieve a list of all surveys
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $surveys = EvaluationToolSurvey::all();
        return $this->showAll($surveys);
    }

    /**
     *  Retrieve a single survey
     *
     * @param EvaluationToolSurvey $survey
     * @return JsonResponse
     */
    public function show(EvaluationToolSurvey $survey): JsonResponse
    {
        return $this->showOne($survey);
    }

    /**
     * Stores a survey record
     *
     * @param EvaluationToolSurveyStoreRequest $request
     * @return JsonResponse
     */
    public function store(EvaluationToolSurveyStoreRequest $request): JsonResponse
    {
        $survey = new EvaluationToolSurvey();
        $survey->fill($request->all());
        $survey->save();

        return $this->showOne($survey->refresh());
    }

    /**
     * Updates a survey record
     *
     * @param EvaluationToolSurveyStoreRequest $request
     * @param EvaluationToolSurvey $survey
     * @return JsonResponse
     */
    public function update(EvaluationToolSurveyStoreRequest $request, EvaluationToolSurvey $survey): JsonResponse
    {
        $survey->fill($request->all());
        $survey->save();

        return $this->showOne($survey->refresh());
    }

    /**
     * Deletes a survey record
     *
     * @param EvaluationToolSurvey $survey
     * @return JsonResponse
     */
    public function destroy(EvaluationToolSurvey $survey): JsonResponse
    {
        if ($survey->survey_steps()->count() > 0) {
            return $this->errorResponse("cannot be deleted, has survey steps", 409);
        }

        $survey->delete();
        return $this->showOne($survey->refresh());
    }
}
