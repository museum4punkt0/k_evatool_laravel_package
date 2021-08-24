<?php

namespace App\Http\Controllers;

namespace Twoavy\EvaluationTool\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Twoavy\EvaluationTool\Http\Requests\EvaluationToolSurveyLocalizationStoreRequest;
use Twoavy\EvaluationTool\Models\EvaluationToolSurveyLocalization;
use Twoavy\EvaluationTool\Traits\EvaluationToolResponse;

class EvaluationToolSurveyLocalizationController extends Controller
{
    use EvaluationToolResponse;
    /**
     *  Retrieve a list of all survey localizations
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $surveyLocalizations = EvaluationToolSurveyLocalization::all();
        return $this->showAll($surveyLocalizations);
    }

    /**
     *  Retrieve a single survey localization
     *
     * @param EvaluationToolSurveyLocalization $surveyLocalization
     * @return JsonResponse
     */
    public function show(EvaluationToolSurveyLocalization $surveyLocalization): JsonResponse
    {
        return $this->showOne($surveyLocalization);
    }

    /**
     * Stores a survey localization record
     *
     * @param EvaluationToolSurveyLocalizationStoreRequest $request
     * @return JsonResponse
     */
    public function store(EvaluationToolSurveyLocalizationStoreRequest $request): JsonResponse
    {
        $surveyLocalization = new EvaluationToolSurveyLocalization();
        $surveyLocalization->fill($request->all());
        $surveyLocalization->save();

        return $this->showOne($surveyLocalization->refresh());
    }

    /**
     * Updates a survey localization record
     *
     * @param EvaluationToolSurveyLocalizationStoreRequest $request
     * @param EvaluationToolSurveyLocalization $surveyLocalization
     * @return JsonResponse
     */
    public function update(EvaluationToolSurveyLocalizationStoreRequest $request, EvaluationToolSurveyLocalization $surveyLocalization): JsonResponse
    {
        $surveyLocalization->fill($request->all());
        $surveyLocalization->save();

        return $this->showOne($surveyLocalization->refresh());
    }

    /**
     * Deletes a survey localization record
     *
     * @param EvaluationToolSurveyLocalization $surveyLocalization
     * @return JsonResponse
     */
    public function destroy(EvaluationToolSurveyLocalization $surveyLocalization): JsonResponse
    {
        // TODO: condition
        // if($surveyLanguage->survey_steps()->count() > 0) {
        //     return $this->errorResponse("cannot be deleted, has survey steps", 409);
        // }

        $surveyLocalization->delete();
        return $this->showOne($surveyLocalization->refresh());
    }
}
