<?php

namespace Twoavy\EvaluationTool\Seeders;

use stdClass;
use Illuminate\Database\Seeder;
use Twoavy\EvaluationTool\Models\EvaluationToolSetting;
use Twoavy\EvaluationTool\Models\EvaluationToolSurveyLanguage;

class EvaluationToolSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
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
