<?php

namespace Twoavy\EvaluationTool\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;
use Twoavy\EvaluationTool\Helpers\EvaluationToolHelper;
use Twoavy\EvaluationTool\Transformers\EvaluationToolSurveyStepTransformer;

class EvaluationToolSurveyStepStoreRequest extends FormRequest
{
    public function __construct(Request $request)
    {
        parent::__construct();
        EvaluationToolHelper::reverseTransform($request, EvaluationToolSurveyStepTransformer::class);
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
        // TODO: check if next step belongs to survey
        $surveyId = request()->segment(4);
        return [
            "survey_element_id" => "required|numeric|exists:evaluation_tool_survey_elements,id",
            "next_step_id"      => "numeric|exists:evaluation_tool_survey_steps,id",
            "published"         => "boolean",
            "publish_up"        => [
                "sometimes",
                "date",
                "date_format:Y-m-d H:i:s",
                "before:publish_down"
            ],
            "publish_down"      => [
                "sometimes",
                "date",
                "date_format:Y-m-d H:i:s",
                "after:publish_up"
            ],
            "name"              => "min:2|max:50"
        ];
    }
}
