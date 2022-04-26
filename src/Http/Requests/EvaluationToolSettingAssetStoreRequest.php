<?php

namespace Twoavy\EvaluationTool\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Twoavy\EvaluationTool\Helpers\EvaluationToolHelper;
use Twoavy\EvaluationTool\Rules\SnakeCase;
use Twoavy\EvaluationTool\Transformers\EvaluationToolSurveyTransformer;

class EvaluationToolSettingAssetStoreRequest extends FormRequest
{
    public function __construct(Request $request)
    {
        parent::__construct();

        $request->request->add([
           'decodedAssetMeta' => json_decode($request->assetMeta, true)
        ]);

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
            'file' => ['required', 'image'],
            'decodedAssetMeta' => ['required', 'array'],
            'decodedAssetMeta.subType' => ['required', Rule::in(['logo', 'icon', 'background'])],
            'name' => ['required', 'string']
        ];
    }
}
