<?php

namespace Twoavy\EvaluationTool\Seeders;

use Illuminate\Database\Seeder;
use Twoavy\EvaluationTool\Factories\EvaluationToolSurveyFactory;
use Twoavy\EvaluationTool\Http\Controllers\EvaluationToolAssetController;
use Twoavy\EvaluationTool\Models\EvaluationToolSurveyStepResultAsset;

class EvaluationToolAssetSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        (new EvaluationToolAssetController)->createSampleAssets();
    }
}
