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
            'default' => false,
            'published' => true,
        ]);

    }
}
