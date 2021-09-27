<?php

namespace Twoavy\EvaluationTool\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Twoavy\EvaluationTool\Factories\EvaluationToolSurveyElementTypeFactory;
use Twoavy\EvaluationTool\Http\Requests\EvaluationToolSurveyElementTypeStoreRequest;
use Twoavy\EvaluationTool\Models\EvaluationToolSurveyElementType;
use Twoavy\EvaluationTool\Traits\EvaluationToolResponse;

class EvaluationToolSurveyElementTypeController extends Controller
{
    use EvaluationToolResponse;

    public function __construct()
    {
        $this->middleware("auth:api")->except(["index", "show"]);
    }

    /**
     * Retrieve a list of all survey element types
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $surveyElementTypes = EvaluationToolSurveyElementType::all();
        return $this->showAll($surveyElementTypes);
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
     * @param EvaluationToolSurveyElementType $surveyElementType
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

    public static function seedSurveyElementTypes()
    {
        DB::table("evaluation_tool_survey_element_types")->truncate();
        EvaluationToolSurveyElementTypeFactory::times(1)->binaryQuestion()->create();
        EvaluationToolSurveyElementTypeFactory::times(1)->multipleChoiceQuestion()->create();
        EvaluationToolSurveyElementTypeFactory::times(1)->simpleText()->create();
        EvaluationToolSurveyElementTypeFactory::times(1)->starRating()->create();
        EvaluationToolSurveyElementTypeFactory::times(1)->yayNay()->create();
        EvaluationToolSurveyElementTypeFactory::times(1)->emoji()->create();
        EvaluationToolSurveyElementTypeFactory::times(1)->video()->create();
        EvaluationToolSurveyElementTypeFactory::times(1)->voiceInput()->create();
        EvaluationToolSurveyElementTypeFactory::times(1)->textInput()->create();
    }
}
