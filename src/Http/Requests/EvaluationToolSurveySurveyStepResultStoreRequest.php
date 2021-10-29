<?php

namespace Twoavy\EvaluationTool\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;
use Twoavy\EvaluationTool\Helpers\EvaluationToolHelper;
use Twoavy\EvaluationTool\Models\EvaluationToolSurveyElement;
use Twoavy\EvaluationTool\Models\EvaluationToolSurveyStep;
use Twoavy\EvaluationTool\Rules\Timecode;
use Twoavy\EvaluationTool\Traits\EvaluationToolResponse;
use Twoavy\EvaluationTool\Transformers\EvaluationToolSurveyStepResultTransformer;

class EvaluationToolSurveySurveyStepResultStoreRequest extends FormRequest
{
    use EvaluationToolResponse;

    public function __construct(Request $request)
    {
        parent::__construct();

        EvaluationToolHelper::reverseTransform($request, EvaluationToolSurveyStepResultTransformer::class);
        if (!$this->surveyStep = EvaluationToolSurveyStep::find($request->survey_step_id)) {
            return $this->errorResponse("survey step not found", 409);
        }
        $this->elementType = ucfirst($this->surveyStep->survey_element->survey_element_type->key);

        if ($this->elementType == "SimpleText") {
//            $this->abortWithMessage("simple text cannot hold results", 409);
        }

        $this->className = 'Twoavy\EvaluationTool\SurveyElementTypes\EvaluationToolSurveyElementType' . ucfirst($this->surveyStep->survey_element->survey_element_type->key);

        if (class_exists($this->className)) {
            $this->className::prepareResultRequest($this->surveyStep->survey_element);
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
     * @return array
     */
    public function rules(): array
    {
        $rules = [
            "survey_step_id"  => ['required', 'exists:evaluation_tool_survey_steps,id'],
            "session_id"      => ['required'],
            "result_language" => ['required', 'min:2', 'max:2', 'exists:evaluation_tool_survey_languages,code'],
            "time"            => [new Timecode()]
        ];

        if (class_exists($this->className)) {
            $element = EvaluationToolSurveyElement::find($this->surveyStep->survey_element_id);
            $rules   = array_merge($rules, $this->className::prepareResultRules($element));
        }

        return $rules;
    }
}
