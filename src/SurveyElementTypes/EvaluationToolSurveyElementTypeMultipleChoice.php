<?php

namespace Twoavy\EvaluationTool\SurveyElementTypes;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use stdClass;
use Twoavy\EvaluationTool\Models\EvaluationToolSurveyLanguage;

class EvaluationToolSurveyElementTypeMultipleChoice
{
    /**
     * @return stdClass
     */
    public static function params(): array
    {
        return [];
    }

    public static function prepareRequest(Request $request) {
        $languageKeys = [];
        if($request->has('params.options')){
            if(is_array($request->params['options'])){
                foreach($request->params['options'] as $key => $value){
                    foreach($value as $language_key => $language_value){
                        $languageKeys[] = $language_key;
                    }
                }
            }
        }
        $request->request->add(['languageKeys' => $languageKeys]);
    }


    /**
     * @return array
     */
    public static function rules(): array
    {
        $maxCount = 10;
        return [
            'params.options' => ['required', 'array', 'min:1'],
            'params.options.*' => ['array'],
            'languageKeys.*' => ['required', 'exists:evaluation_tool_survey_languages,code'],
            'params.min_elements' => ['integer', 'min:1', 'max:'.$maxCount],
            'params.max_elements' => ['integer', 'min:1', 'max:'.$maxCount, 'gte:min_elements']
        ];
    }
}
