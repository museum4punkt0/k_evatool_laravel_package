<?php

namespace Twoavy\EvaluationTool\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Twoavy\EvaluationTool\Helpers\EvaluationToolHelper;
use Twoavy\EvaluationTool\Transformers\EvaluationToolSurveyElementTransformer;

class EvaluationToolSurveyElementStoreRequest extends FormRequest
{
    public function __construct(Request $request)
    {
        parent::__construct();

        // reverse request keys through transformer
        EvaluationToolHelper::reverseTransform($request, EvaluationToolSurveyElementTransformer::class);

        if ($request->has('survey_element_type')) {
            $this->elementType = ucfirst($request->survey_element_type);
            $this->className   = 'Twoavy\EvaluationTool\SurveyElementTypes\EvaluationToolSurveyElementType' . ucfirst($request->survey_element_type);
            if (class_exists($this->className)) {
                if (method_exists($this->className, "prepareRequest")) {
                    $this->className::prepareRequest($request);
                }
                if (method_exists($this->className, "checkCompleteLanguages")) {
                    $this->className::checkCompleteLanguages($request);
                }
            }
        }

//        print_r($request->all());
    }

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public
    function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public
    function rules(): array
    {
        $rules = [
            "name"                => "required|min:2|max:100",
            "survey_element_type" => [
                "required",
                "exists:evaluation_tool_survey_element_types,key"
            ]
        ];

        if (class_exists($this->className)) {
            return array_merge($rules, $this->className::rules());
        }
        return $rules;
    }
}
