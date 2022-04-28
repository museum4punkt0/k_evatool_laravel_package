<?php

namespace Twoavy\EvaluationTool\Helpers;

use stdClass;
use Twoavy\EvaluationTool\Models\EvaluationToolSetting;
use Twoavy\EvaluationTool\Models\EvaluationToolSurveyLanguage;

class EvaluationToolInitialDataHelper
{
    /**
     *  ensures the initial data exists
     *
     * @return void
     */
    public static function ensureInitialData(): void
    {
        self::ensureLanguage();
        self::ensureSetting();
    }

    /**
     *  checks whether at least one language exists, if not creates initial one
     *
     * @return void
     */
    protected static function ensureLanguage(): void
    {
        if (!EvaluationToolSurveyLanguage::first()) {
            EvaluationToolSurveyLanguage::create([
                'code'      => 'de',
                'sub_code'  => 'de_DE',
                'title'     => 'Deutsch',
                'default'   => true,
                'published' => true,
            ]);
        }
    }

    /**
     * checks whether at least one setting exists, if not creates initial one
     *
     * @return void
     */
    protected static function ensureSetting(): void
    {
        if (!EvaluationToolSetting::first()) {
            $settings = new stdClass();
            EvaluationToolSetting::create([
                'name'     => 'Default Configuration',
                'default'  => true,
                'settings' => $settings
            ]);
        }
    }
}
