<?php

namespace Twoavy\EvaluationTool\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Twoavy\EvaluationTool\Models\EvaluationToolSurvey;

class EvaluationToolSurveyFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = EvaluationToolSurvey::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        return [
            "name"        => ucfirst($this->faker->word),
            "description" => $this->faker->boolean() ? ucfirst((string)$this->faker->words($this->faker->numberBetween(1, 10), true)) : null
        ];
    }
}
