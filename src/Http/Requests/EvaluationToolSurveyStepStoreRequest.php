<?php

namespace Twoavy\EvaluationTool\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Twoavy\EvaluationTool\Helpers\EvaluationToolHelper;
use Twoavy\EvaluationTool\Models\EvaluationToolSurvey;
use Twoavy\EvaluationTool\Models\EvaluationToolSurveyElement;
use Twoavy\EvaluationTool\Models\EvaluationToolSurveyStep;
use Twoavy\EvaluationTool\Transformers\EvaluationToolSurveyStepTransformer;

class EvaluationToolSurveyStepStoreRequest extends FormRequest
{
    public function __construct(Request $request)
    {
        parent::__construct();
        EvaluationToolHelper::reverseTransform($request, EvaluationToolSurveyStepTransformer::class);

        $this->surveyId = request()->segment(4);
        if ($request->has('next_step_id')) {
            if ($nextStepSurveyId = EvaluationToolSurveyStep::find($request->next_step_id)) {
                $request->request->add(["next_step_survey_id" => $nextStepSurveyId->survey_id]);
            }
        }

        // get steps already used in survey
        $usedStepIds = [];
        if ($request->has("survey_id")) {
            if ($survey = EvaluationToolSurvey::find($request->has("survey_id"))) {
                $excludeSurveyStep = null;
                if ($request->has("id")) {
                    $excludeSurveyStep = EvaluationToolSurveyStep::find($request->id);
                }
                $usedStepIds = Arr::flatten(EvaluationToolHelper::getUsedSteps($survey, $excludeSurveyStep));
            }
        }

        $request->request->add([
            "used_step_ids" => $usedStepIds
        ]);

        if ($request->has('result_based_next_steps')) {
            $this->surveyElement = EvaluationToolSurveyElement::find($request->survey_element_id);
            $this->className     = 'Twoavy\EvaluationTool\SurveyElementTypes\EvaluationToolSurveyElementType' . ucfirst
                ($this->surveyElement->survey_element_type->key);
            if (class_exists($this->className)) {
                $this->className::validateResultBasedNextSteps($request, $this->surveyElement);
            }
        }
    }

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @param Request $request
     * @return array
     */
    public function rules(Request $request): array
    {

        // Todo: Validate result based next steps

        return [
            "survey_element_id"                => "required|numeric|exists:evaluation_tool_survey_elements,id",
            "next_step_id"                     => "nullable|numeric|exists:evaluation_tool_survey_steps,id",
            "next_step_survey_id"              => "in:" . $this->surveyId,
            "result_based_next_steps"          => "array",
            "result_based_next_steps.*.stepId" => [
                "required",
                "numeric",
                "exists:evaluation_tool_survey_steps,id",
                Rule::notIn([$request->id])
            ],
            "published"                        => "boolean",
            // "publish_up"                       => [
            //     "sometimes",
            //     "nullable",
            //     "date",
            //     "date_format:Y-m-d H:i:s",
            //     "before:publish_down"
            // ],
            // "publish_down"                     => [
            //     "sometimes",
            //     "nullable",
            //     "date",
            //     "date_format:Y-m-d H:i:s",
            //     "after:publish_up"
            // ],
            "name"                             => "min:2|max:50"
        ];
    }
}
