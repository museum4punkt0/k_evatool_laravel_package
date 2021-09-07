<?php

namespace Twoavy\EvaluationTool\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;
use Twoavy\EvaluationTool\Models\EvaluationToolSurvey;
use Twoavy\EvaluationTool\Models\EvaluationToolSurveyElement;
use Twoavy\EvaluationTool\Models\EvaluationToolSurveyStep;

class EvaluationToolSurveyStepFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = EvaluationToolSurveyStep::class;

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

        $surveyId = EvaluationToolSurvey::all()->random(1)[0]->id;

        // check if a potential next step is available
        $nextStepId = null;
        if ($nextStep = EvaluationToolSurveyStep::where("survey_id", $surveyId)->first()) {
            $nextStepId = $nextStep->id;
        }

        $surveyElement = EvaluationToolSurveyElement::all()->random(1)[0];

        return [
            "name"              => $surveyElement->name,
            "survey_element_id" => $surveyElement->id,
            "survey_id"         => $surveyId,
            "next_step_id"      => $nextStepId,
            "published"         => $this->faker->boolean(80),
            "publish_up"        => $publishPeriod ? $publishUp : null,
            "publish_down"      => $publishPeriod ? $publishDown : null,
        ];
    }
}
