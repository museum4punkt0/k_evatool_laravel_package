<?php

namespace Twoavy\EvaluationTool\Seeders\demo;

use Illuminate\Database\Seeder;
use Ramsey\Uuid\Uuid;
use Twoavy\EvaluationTool\Factories\EvaluationToolSurveyElementFactory;
use Twoavy\EvaluationTool\Factories\EvaluationToolSurveyFactory;
use Twoavy\EvaluationTool\Factories\EvaluationToolSurveyStepFactory;
use Twoavy\EvaluationTool\Models\EvaluationToolAsset;
use Twoavy\EvaluationTool\Models\EvaluationToolSurvey;
use Twoavy\EvaluationTool\Models\EvaluationToolSurveyElement;
use Twoavy\EvaluationTool\Seeders\EvaluationToolSeeder;

class EvaluationToolDemoSurveyAllElementTypes extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // create survey
        EvaluationToolSurveyFactory::times(1)->withName("Umfrage mit allen Elementtypen", "Umfrage mit allen Elementtypen in verschiedenen Konfigurationen")
            ->create();
        $surveyId = EvaluationToolSurvey::all()->last()->id;


        // create elements
        EvaluationToolSurveyElementFactory::times(1)->simpleText([
            "text" => [
                "de" => "Willkommen bei dieser Umfrage.",
                "en" => "Welcome to this survey.",
            ],
        ], "Einleitungstext für diese große Umfrage", "Keine Antwortmöglichkeit")->create();

        $introId = EvaluationToolSurveyElement::all()->last()->id;

        EvaluationToolSurveyElementFactory::times(1)->binary([
            "question"   => [
                "de" => "Gefällt Dir das Tool?",
                "en" => "Do you like the tool?",
            ],
            "trueValue"  => "ja",
            "falseValue" => "nein",
            "trueLabel"  => ["de" => "ja", "en" => "yes"],
            "falseLabel" => ["de" => "nein", "en" => "no"],
        ], "Binary Frage", "ja oder nein")->create();

        $binaryId = EvaluationToolSurveyElement::all()->last()->id;

        EvaluationToolSurveyElementFactory::times(1)->multipleChoice([
            "question"      => [
                "de" => "Wie bist Du auf dieses Tool aufmerksam geworden?",
                "en" => "How did you get to know this tool?",
            ],
            "options"       => [
                [

                    'value'  => 'Newsletter',
                    'labels' => [
                        "de" => "Newsletter",
                        "en" => "newsletter",
                    ],
                ],
                [
                    'value'  => 'Empfehlung',
                    'labels' => [
                        "de" => "Empfehlung",
                        "en" => "recommendation",
                    ],
                ],
            ],
            "minSelectable" => 1,
            "maxSelectable" => 1,
        ], "Mulitple Choice", "Nur eine Antwort möglich")->create();

        $multipleChoiceId = EvaluationToolSurveyElement::all()->last()->id;

        EvaluationToolSurveyElementFactory::times(1)->starRating([
            "question"            => [
                "de" => "Wie zufrieden bist Du mit dem Registrierungsprozess?",
                "en" => "How satiesfied are you with the registration process?",
            ],
            "allowHalfSteps"      => false,
            "numberOfStars"       => 7,
            "meaningLowestValue"  => "sehr_unzufrieden",
            "meaningHighestValue" => "sehr_zufrieden",
            "lowestValueLabel"    => ["de" => "sehr unzufrieden", "en" => "very unsatisfied"],
            "middleValueLabel"    => ["de" => "neutral", "en" => "neutral"],
            "highestValueLabel"   => ["de" => "sehr zufrieden", "en" => "very satisfied"],
        ], "Sterne-Bewertung", "Von sehr unzufrieden bis sehr zufrieden")->create();

        $starRatingId = EvaluationToolSurveyElement::all()->last()->id;

        EvaluationToolSurveyElementFactory::times(1)->yayNay([
            "question"   => [
                "de" => "Gefällt Dir dieses Bild?",
                "en" => "Do you like this image",
            ],
            "trueValue"  => "ja",
            "falseValue" => "nein",
            "trueLabel"  => ["de" => "Ja", "en" => "Yes"],
            "falseLabel" => ["de" => "Nein", "en" => "No"],
            "assetIds"   => EvaluationToolAsset::where("mime", "LIKE", 'image/%')->get()->take(3)->pluck("id"),

        ], "Bildbewertung", "Bewertung von Bildern")->create();

        $yayNayId = EvaluationToolSurveyElement::all()->last()->id;

        EvaluationToolSurveyElementFactory::times(1)->emoji([
            "question" => [
                "de" => "Wie zufrieden bist Du mit der barrierefreien Nutzung des Tools?",
                "en" => "How happy are you with the accessibility of this tool?",
            ],
            "emojis"   => [
                ["type" => "😁", "meaning" => "satisfied"],
                ["type" => "🤔", "meaning" => "neutral"],
                ["type" => "😥", "meaning" => "not_satisfied"],
            ],
        ], "Öffnungszeiten", "Zufriedenheit Öffnungszeiten")->create();

        $emojiId = EvaluationToolSurveyElement::all()->last()->id;

        $subElementIds = [];
        $i             = 0;
        while ($i < 3) {
            EvaluationToolSurveyElementFactory::times(1)->starRating([
                "question"            => [
                    "de" => "Eine auf deutsch formulierte Frage",
                    "en" => "A question presented in English",
                ],
                "allowHalfSteps"      => false,
                "numberOfStars"       => 5,
                "meaningLowestValue"  => "very_unhappy",
                "meaningHighestValue" => "very_happy",
                "lowestValueLabel"    => ["de" => "sehr unglücklich", "en" => "very unhappy"],
                "middleValueLabel"    => ["de" => "neutral", "en" => "neutral"],
                "highestValueLabel"   => ["de" => "sehr glücklich", "en" => "very happy"],
            ], "Sterne-Bewertung", "Von sehr unglücklich bis sehr glücklich")->create();
            $i++;
            $subElementIds[] = EvaluationToolSurveyElement::all()->last()->id;
        }
        EvaluationToolSurveyElementFactory::times(1)->video([
            "videoAssetId" => EvaluationToolAsset::where("mime", "LIKE", 'video/%')->first()->id,
        ], "Syncing Video")->create();
        $videoId = EvaluationToolSurveyElement::all()->last()->id;


        EvaluationToolSurveyElementFactory::times(1)->textInput([
            "question" => [
                "de" => "Welches Feature hat Dir am besten gefallen?",
                "en" => "What feature did you like the most?",
            ],
        ], "Lieblingsfeature", "offene Frage zum Lieblingsfeature")->create();

        $textInputId = EvaluationToolSurveyElement::all()->last()->id;

        EvaluationToolSurveyElementFactory::times(1)->multipleChoice([
            "question"      => [
                "de" => "Welche Umfragetypen sind deiner Meinung nach am nützlichsten?",
                "en" => "Which element types are the most useful in your opinion?",
            ],
            "options"       => [
                [
                    'value'  => 'SimpleText',
                    'labels' => [
                        "de" => "SimpleText",
                        "en" => "SimpleText",
                    ],
                ],
                [
                    'value'  => 'Binary',
                    'labels' => [
                        "de" => "Binary",
                        "en" => "Binary",
                    ],
                ],
                [
                    'value'  => 'MultipleChoice',
                    'labels' => [
                        "de" => "MultipleChoice",
                        "en" => "MultipleChoice",
                    ],
                ],
                [
                    'value'  => 'YayNay',
                    'labels' => [
                        "de" => "YayNay",
                        "en" => "YayNay",
                    ],
                ],
                [
                    'value'  => 'Video',
                    'labels' => [
                        "de" => "Video",
                        "en" => "Video",
                    ],
                ],
                [
                    'value'  => 'StarRating',
                    'labels' => [
                        "de" => "StarRating",
                        "en" => "StarRating",
                    ],
                ],
                [
                    'value'  => 'Emoji',
                    'labels' => [
                        "de" => "Emoji",
                        "en" => "Emoji",
                    ],
                ],
                [
                    'value'  => 'TextInput',
                    'labels' => [
                        "de" => "TextInput",
                        "en" => "TextInput",
                    ],
                ],
                [
                    'value'  => 'VoiceInput',
                    'labels' => [
                        "de" => "VoiceInput",
                        "en" => "VoiceInput",
                    ],
                ],

            ],
            "minSelectable" => 2,
            "maxSelectable" => 4,
        ], "Elementtypen", "zwei bis vier Antworten möglich")->create();

        $elementTypesMultipleChoiceId = EvaluationToolSurveyElement::all()->last()->id;

        EvaluationToolSurveyElementFactory::times(1)->voiceInput([
            "question" => [
                "de" => "Hast Du noch Verbesserungsvorschläge?",
                "en" => "Do you have any suggestions for improvement?",
            ],
        ], "Verbesserungsvorschläge", "Verbesserungsvorschläge in Sprachform")->create();

        $voiceInputId = EvaluationToolSurveyElement::all()->last()->id;


        // create steps
        EvaluationToolSurveyStepFactory::times(1)->withData("Einleitung", $introId, $surveyId, null, null, true)->create();
        $introStep = EvaluationToolSeeder::getLatestStep();
        EvaluationToolSurveyStepFactory::times(1)->withData("Binary Frage", $binaryId, $surveyId)->create();
        $binaryStep = EvaluationToolSeeder::getLatestStep();
        EvaluationToolSurveyStepFactory::times(1)->withData("Einfach-Auswahl", $multipleChoiceId, $surveyId)->create();
        $multipleChoiceStep = EvaluationToolSeeder::getLatestStep();
        EvaluationToolSurveyStepFactory::times(1)->withData("Registrierungsprozess", $starRatingId, $surveyId)->create();
        $starRatingStep = EvaluationToolSeeder::getLatestStep();
        EvaluationToolSurveyStepFactory::times(1)->withData("Bildbewertung", $yayNayId, $surveyId)->create();
        $yayNayStep = EvaluationToolSeeder::getLatestStep();
        EvaluationToolSurveyStepFactory::times(1)->withData("Barrierefreiheit", $emojiId, $surveyId)->create();
        $emojiStep = EvaluationToolSeeder::getLatestStep();

        $timebasedSteps = [];
        foreach ($subElementIds as $s => $subElementId) {
            EvaluationToolSurveyStepFactory::times(1)->withData("Bewertung " . ($s + 1), $subElementId, $surveyId)->create();
            $subStep          = EvaluationToolSeeder::getLatestStep();
            $timebasedSteps[] = [
                "uuid"                => Uuid::uuid4(),
                "stepId"              => $subStep->id,
                "timecode"            => "00:00:" . sprintf('%02d', (($s + 1) * 3 + pow($s, 2))) . ":00",
                "stopsVideo"          => true,
                "description"         => "Beschreibung " . ($s + 1),
                "displayTime"         => 2,
                "allowChangingAnswer" => false,
            ];
        }

        EvaluationToolSurveyStepFactory::times(1)->withData("Video", $videoId, $surveyId, null, $timebasedSteps)->create();
        $videoStep = EvaluationToolSeeder::getLatestStep();
        EvaluationToolSurveyStepFactory::times(1)->withData("ElementTypen", $elementTypesMultipleChoiceId, $surveyId)->create();
        $elementTypesMultipleChoiceStep = EvaluationToolSeeder::getLatestStep();
        EvaluationToolSurveyStepFactory::times(1)->withData("Lieblingsfeature", $textInputId, $surveyId)->create();
        $textInputStep = EvaluationToolSeeder::getLatestStep();
        EvaluationToolSurveyStepFactory::times(1)->withData("Verbesserungsvorschläge", $voiceInputId, $surveyId)->create();
        $voiceInputStep = EvaluationToolSeeder::getLatestStep();

        // connect steps
        $introStep->next_step_id = $binaryStep->id;
        $introStep->save();

        $binaryStep->next_step_id = $multipleChoiceStep->id;
        $binaryStep->save();

        $multipleChoiceStep->next_step_id = $starRatingStep->id;
        $multipleChoiceStep->save();

        $starRatingStep->next_step_id = $emojiStep->id;
        $starRatingStep->save();

        $emojiStep->next_step_id = $yayNayStep->id;
        $emojiStep->save();

        $yayNayStep->next_step_id = $videoStep->id;
        $yayNayStep->save();

        $videoStep->next_step_id = $elementTypesMultipleChoiceStep->id;
        $videoStep->save();

        $elementTypesMultipleChoiceStep->next_step_id = $textInputStep->id;
        $elementTypesMultipleChoiceStep->save();

        $textInputStep->next_step_id = $voiceInputStep->id;
        $textInputStep->save();
    }
}
