<?php

namespace Twoavy\EvaluationTool\SurveyElementTypes;

use Faker\Factory;
use Faker\Generator;
use Illuminate\Http\Request;
use StdClass;
use Twoavy\EvaluationTool\Helpers\EvaluationToolHelper;

/**
 * @property Generator $faker
 * @property $primaryLanguage
 * @property $secondaryLanguages
 */

class EvaluationToolSurveyElementTypeBase
{

    public function __construct()
    {
        $this->faker              = Factory::create();
        $this->primaryLanguage    = EvaluationToolHelper::getPrimaryLanguage();
        $this->secondaryLanguages = EvaluationToolHelper::getSecondaryLanguages();
    }
}
