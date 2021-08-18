<?php

namespace Twoavy\EvaluationTool\Seeders;

use Illuminate\Database\Seeder;
use Twoavy\EvaluationTool\Models\EvaluationToolSurveyElementType;

class EvaluationToolSurveyElementTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Todo: Rework to use factory
        EvaluationToolSurveyElementType::create([
            'name' => 'test element type',
            'description' => 'test description',
            'params' => new \StdClass,
        ]);
    }
}
