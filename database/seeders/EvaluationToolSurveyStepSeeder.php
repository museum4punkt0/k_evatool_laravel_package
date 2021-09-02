<?php

namespace Twoavy\EvaluationTool\Seeders;

use Illuminate\Database\Seeder;
use Twoavy\EvaluationTool\Factories\EvaluationToolSurveyStepFactory;

class EvaluationToolSurveyStepSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $i = 0;
        while ($i < 25) {
            EvaluationToolSurveyStepFactory::times(10)->create();
            $i++;
        }
    }
}
