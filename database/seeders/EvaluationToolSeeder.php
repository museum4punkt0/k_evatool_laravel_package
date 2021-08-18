<?php

namespace Twoavy\EvaluationTool\Seeders;

use Illuminate\Database\Seeder;

class EvaluationToolSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            EvaluationToolSurveySeeder::class,
            EvaluationToolSurveyLanguageSeeder::class,
            EvaluationToolSurveyElementTypeSeeder::class,
            EvaluationToolSurveyLocalizationSeeder::class
        ]);
    }
}
