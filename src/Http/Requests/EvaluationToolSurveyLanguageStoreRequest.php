<?php

namespace Twoavy\EvaluationTool\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EvaluationToolSurveyLanguageStoreRequest extends FormRequest
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
     * @return array
     */
    public function rules(): array
    {
        // TODO: rules
        return [
            // "name"        => "required|min:2|max:100",
            // "description" => "max:500",
            // "published"   => "boolean",
        ];
    }
}
