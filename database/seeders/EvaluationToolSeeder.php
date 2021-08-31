<?php

namespace Twoavy\EvaluationTool\Seeders;

use Illuminate\Database\Seeder;
use Twoavy\EvaluationTool\Http\Controllers\EvaluationToolSurveyElementTypeController;

class EvaluationToolSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        EvaluationToolSurveyElementTypeController::seedSurveyElementTypes();

        $this->call([
            EvaluationToolSurveySeeder::class,
            EvaluationToolSurveyLanguageSeeder::class,
            EvaluationToolSurveyElementSeeder::class,
            EvaluationToolSurveyLocalizationSeeder::class,
            EvaluationToolSurveyStepSeeder::class
        ]);
    }
}
