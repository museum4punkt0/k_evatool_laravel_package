<?php

namespace Twoavy\EvaluationTool\Seeders;

use Illuminate\Database\Seeder;
use Twoavy\EvaluationTool\Models\EvaluationToolSetting;
use Twoavy\EvaluationTool\Models\EvaluationToolSurvey;
use Twoavy\EvaluationTool\Seeders\demo\EvaluationToolDemoSurveyAllElementTypes;
use Twoavy\EvaluationTool\Seeders\demo\EvaluationToolDemoSurveyResultBased;
use Twoavy\EvaluationTool\Seeders\demo\EvaluationToolDemoSurveySimpleLinear;
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
            EvaluationToolDemoSurveyAllElementTypes::class,
            EvaluationToolDemoSurveyResultBased::class,
        ]);

        EvaluationToolSurvey::all()->each(function ($survey) {
            $survey->setting_id = EvaluationToolSetting::first()->id;
            $survey->save();
        });
    }
}
