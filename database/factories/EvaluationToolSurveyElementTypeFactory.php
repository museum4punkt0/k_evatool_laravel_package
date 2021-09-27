<?php

namespace Twoavy\EvaluationTool\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use StdClass;
use Twoavy\EvaluationTool\Models\EvaluationToolSurveyElementType;
use Twoavy\EvaluationTool\SurveyElementTypes\EvaluationToolSurveyElementTypeEmoji;
use Twoavy\EvaluationTool\SurveyElementTypes\EvaluationToolSurveyElementTypeMultipleChoice;
use Twoavy\EvaluationTool\SurveyElementTypes\EvaluationToolSurveyElementTypeStarRating;
use Twoavy\EvaluationTool\SurveyElementTypes\EvaluationToolSurveyElementTypeTextInput;
use Twoavy\EvaluationTool\SurveyElementTypes\EvaluationToolSurveyElementTypeVideo;
use Twoavy\EvaluationTool\SurveyElementTypes\EvaluationToolSurveyElementTypeVoiceInput;
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
                'description' => 'Yay nay description',
                'params'      => EvaluationToolSurveyElementTypeYayNay::typeParams(),
            ];
        });
    }

    public function emoji(): EvaluationToolSurveyElementTypeFactory
    {
        return $this->state(function () {
            return [
                'id'          => 6,
                'key'         => 'emoji',
                'name'        => 'Emoji',
                'description' => 'Emoji description',
                'params'      => EvaluationToolSurveyElementTypeEmoji::typeParams(),
            ];
        });
    }

    public function video(): EvaluationToolSurveyElementTypeFactory
    {
        return $this->state(function () {
            return [
                'id'          => 7,
                'key'         => 'video',
                'name'        => 'Video',
                'description' => 'Video description',
                'params'      => EvaluationToolSurveyElementTypeVideo::typeParams(),
            ];
        });
    }

    public function voiceInput(): EvaluationToolSurveyElementTypeFactory
    {
        return $this->state(function () {
            return [
                'id'          => 8,
                'key'         => 'voiceInput',
                'name'        => 'Voice Input',
                'description' => 'Audio input from user voice recording',
                'params'      => EvaluationToolSurveyElementTypeVoiceInput::typeParams(),
            ];
        });
    }

    public function textInput(): EvaluationToolSurveyElementTypeFactory
    {
        return $this->state(function () {
            return [
                'id'          => 9,
                'key'         => 'textInput',
                'name'        => 'Text Input',
                'description' => 'Text input from user',
                'params'      => EvaluationToolSurveyElementTypeTextInput::typeParams(),
            ];
        });
    }
}
