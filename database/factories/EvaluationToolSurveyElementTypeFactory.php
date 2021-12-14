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
                'id'           => 1,
                'key'          => 'binary',
                'name'         => 'Binary Question',
                'descriptions' => [
                    "title"       => [
                        "de" => "Binäre Auswahl",
                        "en" => "Binary selection"
                    ],
                    "description" => [
                        "de" => 'Ideal für die Abfrage im Sinne von "ja" und "nein"',
                        "en" => 'Most suitable for simple "yes" or "no" options'
                    ]
                ],
                'params'       => new StdClass,
            ];
        });
    }

    public function multipleChoiceQuestion(): EvaluationToolSurveyElementTypeFactory
    {
        return $this->state(function () {
            return [
                'id'           => 2,
                'key'          => 'multipleChoice',
                'name'         => 'Multiple Choice Question',
                'descriptions' => [
                    "title"       => [
                        "de" => "Multiple-Choice",
                        "en" => "Multiple choice"
                    ],
                    "description" => [
                        "de" => "Ermöglicht die Abfrage von einer oder mehreren Optionen.",
                        "en" => "Allows the user to choose between one ore more options."
                    ]
                ],
                'params'       => EvaluationToolSurveyElementTypeMultipleChoice::typeParams(),
            ];
        });
    }

    public function simpleText(): EvaluationToolSurveyElementTypeFactory
    {
        return $this->state(function () {
            return [
                'id'           => 3,
                'key'          => 'simpleText',
                'name'         => 'Simple text',
                'descriptions' => [
                    "title"       => [
                        "de" => "Infotext",
                        "en" => "Informational text"
                    ],
                    "description" => [
                        "de" => "Für Einleitungen und Erklärungen. Der Benutzer kann hier keine Antwort abgeben.",
                        "en" => "For introductory or explanatory text. User cannot give feedback."
                    ]
                ],
                'params'       => new StdClass(),
            ];
        });
    }

    public function starRating(): EvaluationToolSurveyElementTypeFactory
    {
        return $this->state(function () {
            return [
                'id'           => 4,
                'key'          => 'starRating',
                'name'         => 'Star rating',
                'descriptions' => [
                    "title"       => [
                        "de" => "Skalen",
                        "en" => "Scales"
                    ],
                    "description" => [
                        "de" => "Zur Abfrage einer Tendenz über ein Skalen-System.",
                        "en" => "Get tendency through a scale system"
                    ]
                ],
                'params'       => EvaluationToolSurveyElementTypeStarRating::typeParams(),
            ];
        });
    }

    public function yayNay(): EvaluationToolSurveyElementTypeFactory
    {
        return $this->state(function () {
            return [
                'id'           => 5,
                'key'          => 'yayNay',
                'name'         => 'Yay nay',
                'descriptions' => [
                    "title"       => [
                        "de" => "Bilder swipen",
                        "en" => "Image swipe"
                    ],
                    "description" => [
                        "de" => "Auswahl der Option durch Wischen nach links oder rechts",
                        "en" => "Option is chosen by swiping left or right"
                    ]
                ],
                'params'       => EvaluationToolSurveyElementTypeYayNay::typeParams(),
            ];
        });
    }

    public function emoji(): EvaluationToolSurveyElementTypeFactory
    {
        return $this->state(function () {
            return [
                'id'           => 6,
                'key'          => 'emoji',
                'name'         => 'Emoji',
                'descriptions' => [
                    "title"       => [
                        "de" => "Emojis",
                        "en" => "Emojis"
                    ],
                    "description" => [
                        "de" => "Bewertung basierend auf Emojis",
                        "en" => "Emoji-based rating"
                    ]
                ],
                'params'       => EvaluationToolSurveyElementTypeEmoji::typeParams(),
            ];
        });
    }

    public function video(): EvaluationToolSurveyElementTypeFactory
    {
        return $this->state(function () {
            return [
                'id'           => 7,
                'key'          => 'video',
                'name'         => 'Video',
                'descriptions' => [
                    "title"       => [
                        "de" => "Video",
                        "en" => "Video"
                    ],
                    "description" => [
                        "de" => "Das Video bietet die Option, dass der User an verschiedenen Stellen des Video Kommentare hinterlassen kann oder zeitbasiert Fragen gestellt bekommt.",
                        "en" => "The video type offers the option for the user to write comments based on the time location within the video. Additionally other survey elements can be presented at given timestamps."
                    ]
                ],
                'params'       => EvaluationToolSurveyElementTypeVideo::typeParams(),
            ];
        });
    }

    public function voiceInput(): EvaluationToolSurveyElementTypeFactory
    {
        return $this->state(function () {
            return [
                'id'           => 8,
                'key'          => 'voiceInput',
                'name'         => 'Voice Input',
                'descriptions' => [
                    "title"       => [
                        "de" => "Sprach-Eingabe",
                        "en" => "Voice input"
                    ],
                    "description" => [
                        "de" => "Kommentare können hier per Sprache aufgezeichnet werden (wie bei einer Sprachnachricht).",
                        "en" => "Comments can be recorded like voice-mail"
                    ]
                ],
                'params'       => EvaluationToolSurveyElementTypeVoiceInput::typeParams(),
            ];
        });
    }

    public function textInput(): EvaluationToolSurveyElementTypeFactory
    {
        return $this->state(function () {
            return [
                'id'           => 9,
                'key'          => 'textInput',
                'name'         => 'Text Input',
                'descriptions' => [
                    "title"       => [
                        "de" => "Freitext-Frage",
                        "en" => "Text input"
                    ],
                    "description" => [
                        "de" => "Der Benutzer kann einen frei verfassten Kommentar eingeben.",
                        "en" => "The user can submit a text-based comment."
                    ]
                ],
                'params'       => EvaluationToolSurveyElementTypeTextInput::typeParams(),
            ];
        });
    }
}
