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
        EvaluationToolSurveyLanguage::create([
            'code' => 'de',
            'sub_code' => 'de_DE',
            'title' => 'Deutsch',
            'default' => true,
            'published' => true,
        ]);
        EvaluationToolSurveyLanguage::create([
            'code' => 'en',
            'sub_code' => 'en_US',
            'title' => 'English',
            'default' => false,
            'published' => false,
        ]);
    }
}
