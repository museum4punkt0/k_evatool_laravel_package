<?php

namespace Twoavy\EvaluationTool\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;
use StdClass;
use Twoavy\EvaluationTool\Models\EvaluationToolSurveyElement;
use Twoavy\EvaluationTool\Models\EvaluationToolSurveyElementType;
use Twoavy\EvaluationTool\Models\EvaluationToolSurveyLanguage;

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

        $params = new StdClass();

        $surveyElementType            = EvaluationToolSurveyElementType::all()->random(1)[0];
        $surveyElementParamsClassName = "Twoavy\EvaluationTool\SurveyElementTypes\EvaluationToolSurveyElementType" . ucfirst($surveyElementType->key);
        if (class_exists($surveyElementParamsClassName)) {
            $surveyElementParamsClass = new $surveyElementParamsClassName;
            $params                   = $surveyElementParamsClass->sampleParams();
        }


        return [
            "name"                   => $surveyElementType->name,
            "survey_element_type_id" => $surveyElementType->id,
            "description"            => $this->faker->boolean() ? ucfirst((string)$this->faker->words($this->faker->numberBetween(1, 10), true)) : null,
            "params"                 => $params,
            "published"              => $this->faker->boolean(80),
            "publish_up"             => $publishPeriod ? $publishUp : null,
            "publish_down"           => $publishPeriod ? $publishDown : null,
        ];
    }

    /**
     * @param null $params
     * @param string $name
     * @param string $description
     * @return Factory
     */
    public function multipleChoice($params = null, string $name = "Name", string $description = ''): Factory
    {
        $publishedLanguages = EvaluationToolSurveyLanguage::where("published")->get();

        return $this->state(function (array $attributes) use ($params, $name, $description) {
            return [
                'survey_element_type_id' => 2,
                'name'                   => $name,
                'description'            => $description,
                'params'                 => $params
            ];
        });
    }

    /**
     * @param null $params
     * @param string $name
     * @param string $description
     * @return Factory
     */
    public function simpleText($params = null, string $name = "Name", string $description = ''): Factory
    {
        return $this->state(function (array $attributes) use ($params, $name, $description) {
            return [
                'survey_element_type_id' => 3,
                "name"                   => $name,
                "description"            => $description,
                'params'                 => $params
            ];
        });
    }

    /**
     * @param null $params
     * @param string $name
     * @param string $description
     * @return Factory
     */
    public function starRating($params = null, string $name = "Name", string $description = ''): Factory
    {
        $publishedLanguages = EvaluationToolSurveyLanguage::where("published")->get();

        return $this->state(function (array $attributes) use ($params, $name, $description) {
            return [
                'survey_element_type_id' => 4,
                'name'                   => $name,
                'description'            => $description,
                'params'                 => $params
            ];
        });
    }

    /**
     * @param null $params
     * @param string $name
     * @param string $description
     * @return Factory
     */
    public function video($params = null, string $name = "Name", string $description = ''): Factory
    {
        return $this->state(function (array $attributes) use ($params, $name, $description) {
            return [
                'survey_element_type_id' => 7,
                'name'                   => $name,
                'description'            => $description,
                'params'                 => $params
            ];
        });
    }

    /**
     * @param null $params
     * @param string $name
     * @param string $description
     * @return Factory
     */
    public function yayNay($params = null, string $name = "Name", string $description = ''): Factory
    {
        return $this->state(function (array $attributes) use ($params, $name, $description) {
            return [
                'survey_element_type_id' => 5,
                'name'                   => $name,
                'description'            => $description,
                'params'                 => $params
            ];
        });
    }
}
