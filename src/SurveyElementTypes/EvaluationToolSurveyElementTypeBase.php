<?php

namespace Twoavy\EvaluationTool\SurveyElementTypes;

use Faker\Factory;
use Faker\Generator;
use Illuminate\Http\Request;
use Illuminate\Http\ResponseTrait;
use StdClass;
use Twoavy\EvaluationTool\Helpers\EvaluationToolHelper;

/**
 * @property Generator $faker
 * @property $primaryLanguage
 * @property $secondaryLanguages
 */
class EvaluationToolSurveyElementTypeBase
{
    use ResponseTrait;

    const QUESTION_RULES = ["max:1500"];
    const TEXT_RULES = ["max:1500"];

    public function __construct()
    {
        $this->faker              = Factory::create();
        $this->primaryLanguage    = EvaluationToolHelper::getPrimaryLanguage();
        $this->secondaryLanguages = EvaluationToolHelper::getSecondaryLanguages();
    }
}
