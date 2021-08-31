<?php

namespace Twoavy\EvaluationTool\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;
use StdClass;
use Twoavy\EvaluationTool\Models\EvaluationToolSurveyElement;
use Twoavy\EvaluationTool\Models\EvaluationToolSurveyElementType;

class EvaluationToolSurveyElementFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = EvaluationToolSurveyElement::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {

        // set publish period
        $publishPeriod = $this->faker->boolean(20);
        if ($publishPeriod) {
            $publishUp   = Carbon::now()->addDays($this->faker->numberBetween(-10, 5))->roundHour();
            $publishDown = Carbon::now()->addDays($this->faker->numberBetween(6, 25))->roundHour();
        }

        return [
            "name"                   => ucfirst($this->faker->word),
            "survey_element_type_id" => EvaluationToolSurveyElementType::all()->random(1)[0]->id,
            "description"            => $this->faker->boolean() ? ucfirst((string)$this->faker->words($this->faker->numberBetween(1, 10), true)) : null,
            "params"                 => new StdClass,
            "published"              => $this->faker->boolean(80),
            "publish_up"             => $publishPeriod ? $publishUp : null,
            "publish_down"           => $publishPeriod ? $publishDown : null,
        ];
    }
}
