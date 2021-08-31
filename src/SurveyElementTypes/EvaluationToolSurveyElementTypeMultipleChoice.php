<?php

namespace Twoavy\EvaluationTool\SurveyElementTypes;

use stdClass;

class EvaluationToolSurveyElementTypeMultipleChoice
{
    /**
     * @return stdClass
     */
    public static function params(): StdClass
    {
        return new StdClass;
    }

    /**
     * @return array
     */
    public static function rules(): array
    {
        return [];
    }
}
