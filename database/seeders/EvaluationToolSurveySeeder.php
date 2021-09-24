<?php

namespace Twoavy\EvaluationTool\Seeders;

use Illuminate\Database\Seeder;
use Twoavy\EvaluationTool\Factories\EvaluationToolSurveyFactory;

class EvaluationToolSurveySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $i = 0;
        while ($i < 10) {
            EvaluationToolSurveyFactory::times(1)->create();
            $i++;
        }
    }
}
