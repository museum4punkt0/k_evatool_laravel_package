<?php

namespace Twoavy\EvaluationTool\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;
use Twoavy\EvaluationTool\Helpers\EvaluationToolHelper;
use Twoavy\EvaluationTool\Models\EvaluationToolSurveyElement;
use Twoavy\EvaluationTool\Models\EvaluationToolSurveyStep;
use Twoavy\EvaluationTool\Traits\EvaluationToolResponse;
use Twoavy\EvaluationTool\Transformers\EvaluationToolSurveyStepResultTransformer;

class EvaluationToolSurveySurveyStepResultStoreRequest extends FormRequest
{
    use EvaluationToolResponse;
    public function __construct(Request $request)
    {
        parent::__construct();

        EvaluationToolHelper::reverseTransform($request, EvaluationToolSurveyStepResultTransformer::class);
        if (!$this->surveyStep = EvaluationToolSurveyStep::find($request->survey_step_id)){
            return $this->errorResponse("survey step not found", 409);
        }
        $this->elementType = ucfirst($this->surveyStep->survey_element->survey_element_type->key);
        $this->className = 'Twoavy\EvaluationTool\SurveyElementTypes\EvaluationToolSurveyElementType' . ucfirst($this->surveyStep->survey_element->survey_element_type->key);
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
     * @return array
     */
    public function rules(): array
    {

        // TODO: rules
        $rules = [
            "survey_step_id" => ['required', 'exists:evaluation_tool_survey_steps']
            // "name"        => "required|min:2|max:100",
            // "description" => "max:500",
            // "published"   => "boolean",
        ];

        if (class_exists($this->className)) {
            $element = EvaluationToolSurveyElement::find($this->surveyStep->survey_element_id);
            array_merge($rules, $this->className::prepareResultRules($element));
        }
        return $rules;
    }
}
