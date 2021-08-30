<?php

namespace Twoavy\EvaluationTool\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use StdClass;
use Twoavy\EvaluationTool\Models\EvaluationToolSurveyElementType;

class EvaluationToolSurveyElementTypeFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = EvaluationToolSurveyElementType::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        return [];
    }

    public function binaryQuestion(): EvaluationToolSurveyElementTypeFactory
    {
        return $this->state(function () {
            return [
                'id'          => 1,
                'name'        => 'Binary Question',
                'description' => 'Binary Question description',
                'params'      => new StdClass,
            ];
        });
    }

    public function multipleChoiceQuestion(): EvaluationToolSurveyElementTypeFactory
    {
        return $this->state(function () {
            return [
                'id'          => 2,
                'name'        => 'Multiple Choice Question',
                'description' => 'Multiple Choice Question description',
                'params'      => new StdClass,
            ];
        });
    }
}
