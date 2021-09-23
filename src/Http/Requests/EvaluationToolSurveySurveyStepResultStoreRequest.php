<?php

namespace Twoavy\EvaluationTool\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;
use Twoavy\EvaluationTool\Helpers\EvaluationToolHelper;
use Twoavy\EvaluationTool\Models\EvaluationToolSurveyStep;
use Twoavy\EvaluationTool\Models\EvaluationToolSurveyElement;
use Twoavy\EvaluationTool\Transformers\EvaluationToolSurveyStepResultTransformer;

class EvaluationToolSurveySurveyStepResultStoreRequest extends FormRequest
{
    public function __construct(Request $request)
    {
        parent::__construct();

        EvaluationToolHelper::reverseTransform($request, EvaluationToolSurveyStepResultTransformer::class);
        $this->surveyStep = EvaluationToolSurveyStep::find($request->survey_step_id);
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
            // "name"        => "required|min:2|max:100",
            // "description" => "max:500",
            // "published"   => "boolean",
        ];

        if (class_exists($this->className)) {
            $element = EvaluationToolSurveyElement::find($this->surveyStep->survey_element_id);
            return array_merge($rules, $this->className::prepareResultRules($element));
        }
        return $rules;
    }
}
