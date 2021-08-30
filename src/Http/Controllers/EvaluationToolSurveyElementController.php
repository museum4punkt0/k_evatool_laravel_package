<?php

namespace Twoavy\EvaluationTool\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Twoavy\EvaluationTool\Http\Requests\EvaluationToolSurveyElementStoreRequest;
use Twoavy\EvaluationTool\Models\EvaluationToolSurveyElement;
use Twoavy\EvaluationTool\Traits\EvaluationToolResponse;

class EvaluationToolSurveyElementController extends Controller
{
    use EvaluationToolResponse;

    /**
     * Retrieve a list of all survey elements
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $surveyElements = EvaluationToolSurveyElement::all();
        return $this->showAll($surveyElements);
    }
     /**
     *  Retrieve a single survey element
     *
     * @param EvaluationToolSurveyElement $surveyElement
     * @return JsonResponse
     */
    public function show(EvaluationToolSurveyElement $surveyElement): JsonResponse
    {
        return $this->showOne($surveyElement);
    }

    /**
     * Stores a survey element record
     *
     * @param EvaluationToolSurveyElementStoreRequest $request
     * @return JsonResponse
     */
    public function store(EvaluationToolSurveyElementStoreRequest $request): JsonResponse
    {
        $surveyElement = new EvaluationToolSurveyElement();
        $surveyElement->fill($request->all());
        $surveyElement->save();

        return $this->showOne($surveyElement->refresh());
    }

    /**
     * Updates a survey element record
     *
     * @param EvaluationToolSurveyElementStoreRequest $request
     * @param EvaluationToolSurveyElement $surveyElement
     * @return JsonResponse
     */
    public function update(EvaluationToolSurveyElementStoreRequest $request, EvaluationToolSurveyElement $surveyElement): JsonResponse
    {
        $surveyElement->fill($request->all());
        $surveyElement->save();

        return $this->showOne($surveyElement->refresh());
    }

    /**
     * Deletes a survey element record
     *
     * @param EvaluationToolSurveyElement $surveyElement
     * @return JsonResponse
     */
    public function destroy(EvaluationToolSurveyElement $surveyElement): JsonResponse
    {
        // TODO: check condition
        // if($surveyElement->survey_steps()->count() > 0) {
        //     return $this->errorResponse("cannot be deleted, has survey steps", 409);
        // }

        $surveyElement->delete();
        return $this->showOne($surveyElement->refresh());
    }
}
