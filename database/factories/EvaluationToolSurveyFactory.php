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
            "name"        => "Umfrage",
            //            "description" => $this->faker->boolean() ? ucfirst((string)$this->faker->words($this->faker->numberBetween(1, 10), true)) : null
            "description" => $this->faker->boolean() ? "Kurze Textbeschreibung der Umfrage" : null
        ];
    }

    /**
     * @param string $name
     * @param string $description
     * @return EvaluationToolSurveyFactory
     */
    public function withName(string $name, string $description = ''): EvaluationToolSurveyFactory
    {
        return $this->state(function (array $attributes) use ($name, $description) {
            return [
                'name'        => $name,
                'description' => $description,
            ];
        });
    }
}
