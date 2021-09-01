<?php

namespace Twoavy\EvaluationTool\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;

class EvaluationToolSurveyElementStoreRequest extends FormRequest
{
    public function __construct(Request $request)
    {
        if ($request->has('survey_element_type')) {
            $this->elementType = ucfirst($request->survey_element_type);
            $this->className = 'Twoavy\EvaluationTool\SurveyElementTypes\EvaluationToolSurveyElementType' . ucfirst($request->survey_element_type);
            if (class_exists($this->className)) {
                $this->className::prepareRequest($request);
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
        // TODO: rules
        $rules    = [
            "name" => "required|min:2|max:100",
            // "description" => "max:500",
            // "published"   => "boolean",
        ];

        return array_merge($rules, $this->className::rules());
    }
}
