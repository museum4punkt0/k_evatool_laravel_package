<?php

namespace Twoavy\EvaluationTool\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Twoavy\EvaluationTool\Http\Requests\EvaluationToolSurveyElementTypeStoreRequest;
use Twoavy\EvaluationTool\Models\EvaluationToolSurveyElementType;
use Twoavy\EvaluationTool\Traits\EvaluationToolResponse;

class EvaluationToolSurveyElementTypeController extends Controller
{
    use EvaluationToolResponse;

    /**
     * Retrieve a list of all survey element types
     * 
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $surveyElementTypes = EvaluationToolSurveyElementType::all();
        return response()->json($surveyElementTypes);
    }


    /**
     *  Retrieve a single survey element type
     *
     * @param EvaluationToolSurveyElementType $surveyElementType
     * @return JsonResponse
     */
    public function show(EvaluationToolSurveyElementType $surveyElementType): JsonResponse
    {
        return $this->showOne($surveyElementType);
    }

    /**
     * Stores a survey element type record
     *
     * @param EvaluationToolSurveyElementTypeStoreRequest $request
     * @return JsonResponse
     */
    public function store(EvaluationToolSurveyElementTypeStoreRequest $request): JsonResponse
    {
        $surveyElementType = new EvaluationToolSurveyElementType();
        $surveyElementType->fill($request->all());
        $surveyElementType->save();

        return $this->showOne($surveyElementType->refresh());
    }

    /**
     * Updates a survey element type record
     *
     * @param EvaluationToolSurveyElementTypeStoreRequest $request
     * @param EvaluationToolSurvey $survey
     * @return JsonResponse
     */
    public function update(EvaluationToolSurveyElementTypeStoreRequest $request, EvaluationToolSurveyElementType $surveyElementType): JsonResponse
    {
        $surveyElementType->fill($request->all());
        $surveyElementType->save();

        return $this->showOne($surveyElementType->refresh());
    }

    /**
     * Deletes a survey element type record
     *
     * @param EvaluationToolSurveyElementType $surveyElementType
     * @return JsonResponse
     */
    public function destroy(EvaluationToolSurveyElementType $surveyElementType): JsonResponse
    {
        // TODO: condition
        // if($survey->survey_steps()->count() > 0) {
        //     return $this->errorResponse("cannot be deleted, has survey steps", 409);
        // }

        $surveyElementType->delete();
        return $this->showOne($surveyElementType->refresh());
    }
}
