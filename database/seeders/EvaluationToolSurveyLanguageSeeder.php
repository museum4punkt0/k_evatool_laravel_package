<?php

namespace Twoavy\EvaluationTool\Seeders;

use Illuminate\Database\Seeder;
use Twoavy\EvaluationTool\Models\EvaluationToolSurveyLanguage;

class EvaluationToolSurveyLanguageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Todo: Rework to use factory (here two factories, one for default language, one for secondary languages
        EvaluationToolSurveyLanguage::create([
            'code' => 'de',
            'sub_code' => 'de_DE',
            'title' => 'Deutsch',
            'default' => true,
            'published' => true,
        ]);

        EvaluationToolSurveyLanguage::create([
            'code' => 'en',
            'sub_code' => 'en_GB',
            'title' => 'English',
            'default' => false,
            'published' => true,
        ]);

        EvaluationToolSurveyLanguage::create([
            'code' => 'fr',
            'sub_code' => 'fr_FR',
            'title' => 'Français',
            'default' => false,
            'published' => true,
        ]);

    }
}
