<?php

namespace Twoavy\EvaluationTool\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Twoavy\EvaluationTool\Helpers\EvaluationToolHelper;
use Twoavy\EvaluationTool\Models\EvaluationToolSurvey;
use Twoavy\EvaluationTool\Models\EvaluationToolSurveyElement;
use Twoavy\EvaluationTool\Models\EvaluationToolSurveyStep;
use Twoavy\EvaluationTool\Rules\Timecode;
use Twoavy\EvaluationTool\SurveyElementTypes\EvaluationToolSurveyElementTypeVideo;
use Twoavy\EvaluationTool\Transformers\EvaluationToolSurveyStepTransformer;

class EvaluationToolSurveyStatsExportRequest extends FormRequest
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
        return [
            "demo"       => "boolean",
            "start"      => "date_format:Y-m-d|before:now|before_or_equal:end",
            "end"        => "date_format:Y-m-d|before:now|after_or_equal:start",
            "execute"    => "boolean",
            "exportType" => [
                "requiredIf:execute,true",
                Rule::in(["xlsx", "json", "csv"])
            ]
        ];
    }
}
