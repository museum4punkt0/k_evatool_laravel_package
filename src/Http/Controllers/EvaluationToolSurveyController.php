<?php

namespace Twoavy\EvaluationTool\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Twoavy\EvaluationTool\Http\Requests\EvaluationToolSurveyStoreAdminLayoutRequest;
use Twoavy\EvaluationTool\Http\Requests\EvaluationToolSurveyStoreRequest;
use Twoavy\EvaluationTool\Models\EvaluationToolSurvey;
use Twoavy\EvaluationTool\Observers\EvaluationToolSurveyObserver;
use Twoavy\EvaluationTool\Traits\EvaluationToolResponse;

class EvaluationToolSurveyController extends Controller
{
    use EvaluationToolResponse;

    public function __construct()
    {
        $this->middleware("auth:api");
    }

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

        EvaluationToolSurveyObserver::assignLanguages($survey, $request);

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

    /**
     * @param EvaluationToolSurvey $survey
     * @param EvaluationToolSurveyStoreAdminLayoutRequest $request
     * @return JsonResponse
     */
    public function updateAdminLayout(EvaluationToolSurvey $survey, EvaluationToolSurveyStoreAdminLayoutRequest $request): JsonResponse
    {
        $survey->admin_layout = $request->admin_layout;
        $survey->save();

        return $this->showOne($survey);

    }

    /**
     * @param EvaluationToolSurvey $survey
     * @return JsonResponse
     */
    public function publishSurvey(EvaluationToolSurvey $survey): JsonResponse
    {
        $survey->published = !$survey->published;
        $survey->save();

        return $this->showOne($survey);
    }

    /**
     * @param EvaluationToolSurvey $survey
     * @return JsonResponse
     */
    public function duplicateSurvey(EvaluationToolSurvey $survey): JsonResponse
    {
        // Todo: Implement replication of survey and survey steps
//        $duplicateSurvey = $survey->replicate()->makeHidden(["survey_steps_count", "survey_results_count"]);
//        $duplicateSurvey->push();

        return $this->showOne($survey);
    }

    public function archiveSurvey(EvaluationToolSurvey $survey): JsonResponse
    {

        // toggles archive state and sets published state to opposite

        $survey->archived = !$survey->archived;
        if ($survey->archived === true) {
            $survey->archived_at = now();
            $survey->published = false;
        } else {
            $survey->archived_at = null;
        }
        $survey->save();

        return $this->showOne($survey);
    }
}
