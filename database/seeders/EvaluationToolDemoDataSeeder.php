<?php

namespace Twoavy\EvaluationTool\Seeders;

use Illuminate\Database\Seeder;
use Twoavy\EvaluationTool\Seeders\Demo\EvaluationToolDemoSurveySimpleLinear;
use Twoavy\EvaluationTool\Seeders\demo\EvaluationToolDemoSurveySimpleVideo;

class EvaluationToolDemoDataSeeder extends Seeder
{
    /**
     * @return void
     */
    public function run()
    {
        $this->call([
            EvaluationToolDemoSurveySimpleLinear::class,
            EvaluationToolDemoSurveySimpleVideo::class,
        ]);
    }
}
