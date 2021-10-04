<?php

namespace Twoavy\EvaluationTool\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;
use Twoavy\EvaluationTool\Helpers\EvaluationToolHelper;
use Twoavy\EvaluationTool\Transformers\EvaluationToolSurveyTransformer;

class EvaluationToolSurveyStoreAdminLayoutRequest extends FormRequest
{
    public function __construct(Request $request)
    {
        parent::__construct();

        // reverse request keys through transformer
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
        // Todo: Full validation including check of id in survey steps of survey
        return [
            "admin_layout" => "sometimes|array"
        ];
    }
}
