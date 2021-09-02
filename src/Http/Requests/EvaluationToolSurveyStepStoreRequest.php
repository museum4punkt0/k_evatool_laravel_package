<?php

namespace Twoavy\EvaluationTool\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;
use Twoavy\EvaluationTool\Helpers\EvaluationToolHelper;
use Twoavy\EvaluationTool\Models\EvaluationToolSurveyStep;
use Twoavy\EvaluationTool\Transformers\EvaluationToolSurveyStepTransformer;

class EvaluationToolSurveyStepStoreRequest extends FormRequest
{
    public function __construct(Request $request)
    {
        parent::__construct();
        EvaluationToolHelper::reverseTransform($request, EvaluationToolSurveyStepTransformer::class);

        $this->surveyId = request()->segment(4);
        if ($request->has('next_step_id')) {
            $nextStepSurveyId = EvaluationToolSurveyStep::find($request->next_step_id)->survey_id;
            $request->request->add(["next_step_survey_id" => $nextStepSurveyId]);
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

        return [
            "survey_element_id"   => "required|numeric|exists:evaluation_tool_survey_elements,id",
            "next_step_id"        => "numeric|exists:evaluation_tool_survey_steps,id",
            "next_step_survey_id" => "required|in:" . $this->surveyId,
            "published"           => "boolean",
            "publish_up"          => [
                "sometimes",
                "date",
                "date_format:Y-m-d H:i:s",
                "before:publish_down"
            ],
            "publish_down"        => [
                "sometimes",
                "date",
                "date_format:Y-m-d H:i:s",
                "after:publish_up"
            ],
            "name"                => "min:2|max:50"
        ];
    }
}
