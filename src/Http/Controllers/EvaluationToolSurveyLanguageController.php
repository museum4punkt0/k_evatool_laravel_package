<?php

namespace Twoavy\EvaluationTool\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Twoavy\EvaluationTool\Http\Requests\EvaluationToolSurveyLanguageStoreRequest;
use Twoavy\EvaluationTool\Models\EvaluationToolSurveyLanguage;
use Twoavy\EvaluationTool\Traits\EvaluationToolResponse;

class EvaluationToolSurveyLanguageController extends Controller
{
    use EvaluationToolResponse;

    /**
     * Retrieve a list of all survey languages
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $surveyLanguages = EvaluationToolSurveyLanguage::all();
        return $this->showAll($surveyLanguages);
    }

    /**
     *  Retrieve a single survey language
     *
     * @param EvaluationToolSurveyLanguage $surveyLanguage
     * @return JsonResponse
     */
    public function show(EvaluationToolSurveyLanguage $surveyLanguage): JsonResponse
    {
        return $this->showOne($surveyLanguage);
    }

    /**
     * Stores a survey language record
     *
     * @param EvaluationToolSurveyLanguageStoreRequest $request
     * @return JsonResponse
     */
    public function store(EvaluationToolSurveyLanguageStoreRequest $request): JsonResponse
    {
        $survey = new EvaluationToolSurveyLanguage();
        $survey->fill($request->all());
        $survey->save();

        return $this->showOne($survey->refresh());
    }

    /**
     * Updates a survey language record
     *
     * @param EvaluationToolSurveyLanguageStoreRequest $request
     * @param EvaluationToolSurveyLanguage $surveyLanguage
     * @return JsonResponse
     */
    public function update(EvaluationToolSurveyLanguageStoreRequest $request, EvaluationToolSurveyLanguage $surveyLanguage): JsonResponse
    {
        $surveyLanguage->fill($request->all());
        $surveyLanguage->save();

        return $this->showOne($surveyLanguage->refresh());
    }

    /**
     * Deletes a survey language record
     *
     * @param EvaluationToolSurveyLanguage $surveyLanguage
     * @return JsonResponse
     */
    public function destroy(EvaluationToolSurveyLanguage $surveyLanguage): JsonResponse
    {
        // TODO: condition
        // if($surveyLanguage->survey_steps()->count() > 0) {
        //     return $this->errorResponse("cannot be deleted, has survey steps", 409);
        // }

        $surveyLanguage->delete();
        return $this->showOne($surveyLanguage->refresh());
    }
}
