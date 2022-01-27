<?php

namespace Twoavy\EvaluationTool\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EvaluationToolSettingStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            "name" => 'required|min:3|max:50',
            "settings" => 'required|array',
            "settings.pageTitle" => 'required|array',
            "settings.pageTitle.*" => 'min:3|max:50'
            //
        ];
    }
}
