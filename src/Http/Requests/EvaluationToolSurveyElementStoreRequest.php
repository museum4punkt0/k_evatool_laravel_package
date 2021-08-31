<?php

namespace Twoavy\EvaluationTool\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;
use Twoavy\EvaluationTool\SurveyElementTypes\EvaluationToolSurveyElementTypeMultipleChoice;
use Illuminate\Support\Arr;

class EvaluationToolSurveyElementStoreRequest extends FormRequest
{
    public function __construct(Request $request)
    {
        if($request->has('survey_element_type')){
            // dd('EvaluationToolSurveyElementType'.ucfirst($request->survey_element_type));
            $className = 'Twoavy\EvaluationTool\SurveyElementTypes\EvaluationToolSurveyElementType'.ucfirst($request->survey_element_type);
            if(class_exists($className)){
                $className::prepareRequest($request);
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
     * @return array
     */
    public function rules(Request $request): array
    {
        // TODO: rules
        $rules = [
            "name"        => "required|min:2|max:100",
            // "description" => "max:500",
            // "published"   => "boolean",
        ];
        $allRules = array_merge($rules, EvaluationToolSurveyElementTypeMultipleChoice::rules());
        return $allRules;
    }
}
