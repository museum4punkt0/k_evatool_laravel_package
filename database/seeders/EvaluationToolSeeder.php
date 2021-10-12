<?php

namespace Twoavy\EvaluationTool\Seeders;

use Illuminate\Database\Seeder;
use Twoavy\EvaluationTool\Http\Controllers\EvaluationToolSurveyElementTypeController;
use Twoavy\EvaluationTool\Models\EvaluationToolSurveyStep;

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
            EvaluationToolSurveyLanguageSeeder::class,
            EvaluationToolAssetSeeder::class,
            EvaluationToolDemoDataSeeder::class
            //            EvaluationToolSurveySeeder::class,
            //            EvaluationToolSurveyElementSeeder::class,
            //            EvaluationToolSurveyLocalizationSeeder::class,
            //            EvaluationToolSurveyStepSeeder::class,
        ]);
    }

    public static function getLatestStep() {
        return EvaluationToolSurveyStep::all()->last();
    }
}
