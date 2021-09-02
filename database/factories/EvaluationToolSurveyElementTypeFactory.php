<?php

namespace Twoavy\EvaluationTool\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use StdClass;
use Twoavy\EvaluationTool\Models\EvaluationToolSurveyElementType;
use Twoavy\EvaluationTool\SurveyElementTypes\EvaluationToolSurveyElementTypeMultipleChoice;
use Twoavy\EvaluationTool\SurveyElementTypes\EvaluationToolSurveyElementTypeStarRating;
use Twoavy\EvaluationTool\SurveyElementTypes\EvaluationToolSurveyElementTypeYayNay;

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
                'key'         => 'binary',
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
                'key'         => 'multipleChoice',
                'name'        => 'Multiple Choice Question',
                'description' => 'Multiple Choice Question description',
                'params'      => EvaluationToolSurveyElementTypeMultipleChoice::typeParams(),
            ];
        });
    }

    public function simpleText(): EvaluationToolSurveyElementTypeFactory
    {
        return $this->state(function () {
            return [
                'id'          => 3,
                'key'         => 'simpleText',
                'name'        => 'Simple text',
                'description' => 'Simple text description',
                'params'      => new StdClass(),
            ];
        });
    }

    public function starRating(): EvaluationToolSurveyElementTypeFactory
    {
        return $this->state(function () {
            return [
                'id'          => 4,
                'key'         => 'starRating',
                'name'        => 'Star rating',
                'description' => 'Star rating description',
                'params'      => EvaluationToolSurveyElementTypeStarRating::typeParams(),
            ];
        });
    }

    public function yayNay(): EvaluationToolSurveyElementTypeFactory
    {
        return $this->state(function () {
            return [
                'id'          => 5,
                'key'         => 'yayNay',
                'name'        => 'Yay nay',
                'description' => 'Star rating description',
                'params'      => EvaluationToolSurveyElementTypeYayNay::typeParams(),
            ];
        });
    }
}
