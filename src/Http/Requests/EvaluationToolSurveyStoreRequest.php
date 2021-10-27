<?php

namespace Twoavy\EvaluationTool\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;

class EvaluationToolSurveyStoreRequest extends FormRequest
{
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
        return [
            "name"        => "required|min:2|max:100",
            "slug"        => "max:100",
            "description" => "max:500",
            "published"   => "boolean",
            "languages"   => "array",
            "languages.*" => ['required', 'min:2', 'max:2', 'exists:evaluation_tool_survey_languages,code']
        ];
    }
}
