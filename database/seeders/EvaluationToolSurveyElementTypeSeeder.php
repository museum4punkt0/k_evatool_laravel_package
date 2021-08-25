<?php

namespace Twoavy\EvaluationTool\Seeders;

use Illuminate\Database\Seeder;
use PhpParser\Node\Expr\Cast\Object_;
use Twoavy\EvaluationTool\Factories\EvaluationToolSurveyElementTypeFactory;

class EvaluationToolSurveyElementTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        EvaluationToolSurveyElementTypeFactory::times(1)->binaryQuestion()->create();
        EvaluationToolSurveyElementTypeFactory::times(1)->multipleChoiceQuestion()->create();
/*
        EvaluationToolSurveyElementType::create([
            'name' => 'test element type',
            'description' => 'test description',
            'params' => new \StdClass,
        ]);
*/
    }
}
