<?php

namespace Twoavy\EvaluationTool\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
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
        return [
            "name"        => ucfirst($this->faker->word),
            "description" => $this->faker->boolean() ? ucfirst((string)$this->faker->words($this->faker->numberBetween(1, 10), true)) : null,
            'params' => new \StdClass,
        ];
    }

    public function binaryQuestion(): EvaluationToolSurveyElementTypeFactory
    {
        return $this->state(function (array $attributes) {
            return [

                'name' => 'binary Question',
                'description' => 'binary Question description',
                'params' => new \StdClass,
            ];
        });
    }

    public function multipleChoiceQuestion(): EvaluationToolSurveyElementTypeFactory
    {
        return $this->state(function (array $attributes) {
            return [

                'name' => 'multiple Choice Question',
                'description' => 'multiple Choice Question description',
                'params' => new \StdClass,
            ];
        });
    }
}
