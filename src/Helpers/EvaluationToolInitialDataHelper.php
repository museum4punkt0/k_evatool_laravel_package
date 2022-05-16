<?php

namespace Twoavy\EvaluationTool\Helpers;

use Illuminate\Support\Facades\Schema;
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
        if(Schema::hasTable("evaluation_tool_survey_languages")) {
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
    }

    /**
     * checks whether at least one setting exists, if not creates initial one
     *
     * @return void
     */
    protected static function ensureSetting(): void
    {
        if(Schema::hasTable("evaluation_tool_settings")) {
            if (!EvaluationToolSetting::first()) {
                // get all languages
                $languages = EvaluationToolSurveyLanguage::all()->pluck("code");

                // init settings
                $settings              = new stdClass();
                $settings->companyName = new StdClass();
                $languages->each(function ($languageKey) use ($settings) {
                    $settings->companyName->{$languageKey} = "Company name";
                });

                EvaluationToolSetting::create([
                    'name'     => 'Default Configuration',
                    'default'  => true,
                    'settings' => $settings
                ]);
            }
        }
    }
}
