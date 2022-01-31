<?php

namespace Twoavy\EvaluationTool\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;
use Twoavy\EvaluationTool\Helpers\EvaluationToolHelper;
use Twoavy\EvaluationTool\Rules\SnakeCase;
use Twoavy\EvaluationTool\Transformers\EvaluationToolSurveyTransformer;

class EvaluationToolSurveyStoreRequest extends FormRequest
{
    public function __construct(Request $request)
    {
        parent::__construct();
        EvaluationToolHelper::reverseTransform($request, EvaluationToolSurveyTransformer::class);

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
        return [
            "name"        => "required|min:3|max:100",
            "slug"        => ["min:3", "max:100", new SnakeCase()],
            "description" => "max:500",
            "published"   => "boolean",
            "languages"   => "array",
            "languages.*" => ['required', 'min:2', 'max:2', 'exists:evaluation_tool_survey_languages,code'],
            "setting_id" => ['exists:evaluation_tool_settings,id']
        ];
    }
}
