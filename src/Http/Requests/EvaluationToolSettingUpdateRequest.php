<?php

namespace Twoavy\EvaluationTool\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;
use Twoavy\EvaluationTool\Helpers\EvaluationToolHelper;
use Twoavy\EvaluationTool\Transformers\EvaluationToolSettingTransformer;

class EvaluationToolSettingUpdateRequest extends FormRequest
{

    public function __construct(Request $request)
    {
        parent::__construct();
        EvaluationToolHelper::reverseTransform($request, EvaluationToolSettingTransformer::class);
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
            "name"                         => 'required|min:3|max:50',
            "settings"                     => 'required|array',
            "settings.pageTitle"           => 'required|array',
            "settings.pageTitle.*"         => 'min:3|max:50',
            "settings.companyName"         => 'required|array',
            "settings.companyName.*"       => 'nullable|min:3|max:50',
            "settings.privacy"             => 'array',
            "settings.logoImage"           => 'nullable|string',
            "settings.backgroundImage"     => 'nullable|string',
            "settings.iconImage"           => 'nullable|string',
            "settings.privacy.*"           => 'nullable|min:3',
            "settings.privacyLink"         => 'nullable|min:3|url',
            "settings.imprint"             => 'array',
            "settings.imprint.*"           => 'nullable|min:3',
            "settings.imprintLink"         => 'nullable|min:3|url',
            "settings.socialDescription"   => 'array',
            "settings.socialDescription.*" => 'nullable|min:3|max:1000',
        ];
    }
}
