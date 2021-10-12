<?php

namespace Twoavy\EvaluationTool\Seeders\demo;

use Illuminate\Database\Seeder;
use Twoavy\EvaluationTool\Factories\EvaluationToolSurveyElementFactory;
use Twoavy\EvaluationTool\Factories\EvaluationToolSurveyFactory;
use Twoavy\EvaluationTool\Factories\EvaluationToolSurveyStepFactory;
use Twoavy\EvaluationTool\Models\EvaluationToolAsset;
use Twoavy\EvaluationTool\Models\EvaluationToolSurvey;
use Twoavy\EvaluationTool\Models\EvaluationToolSurveyElement;

class EvaluationToolDemoSurveyAllElementTypes extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        EvaluationToolSurveyElementFactory::times(1)->simpleText([
            "text" => [
                "de" => "Willkommen bei dieser Umfrage.",
                "en" => "Welcome to this survey.",
            ],
        ], "Einleitungstext für diese große Umfrage", "Keine Antwortmöglichkeit")->create();

        $introId = EvaluationToolSurveyElement::all()->last()->id;

        EvaluationToolSurveyElementFactory::times(1)->multipleChoice([
            "question" => [
                "de" => "Wie bist Du auf dieses Tool aufmerksam geworden?",
                "en" => "How did you get to know this tool?",
            ],
            "options" => [
                [

                    'value' => 'Newsletter',
                    'labels' => [
                        "de" => "Newsletter",
                        "en" => "newsletter",
                    ],
                ],
                [
                    'value' => 'Empfehlung',
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
            "question" => [
                "de" => "Wie zufrieden bist Du mit dem Registrierungsprozess?",
                "en" => "How satiesfied are you with the registration process?",
            ],
            "allowHalfSteps" => false,
            "numberOfStars" => 7,
            "meaningLowestValue" => "sehr unzufrieden",
            "meaningHighestValue" => "sehr zufrieden",
            "lowestValueLabel" => ["de" => "sehr unzufrieden", "en" => "very unsatisfied"],
            "middleValueLabel" => ["de" => "neutral", "en" => "neutral"],
            "highestValueLabel" => ["de" => "sehr zufrieden", "en" => "very satisfied"],
        ], "Sterne-Bewertung", "Von sehr unzufrieden bis sehr zufrieden")->create();

        $starRatingId = EvaluationToolSurveyElement::all()->last()->id;

        EvaluationToolSurveyElementFactory::times(1)->yayNay([
            "question" => [
                "de" => "Gefällt Dir dieses Bild?",
                "en" => "Do you like this image",
            ],
            "trueValue" => "ja",
            "falseValue" => "nein",
            "trueLabel" => ["de" => "Ja", "en" => "Yes"],
            "falseLabel" => ["de" => "Nein", "en" => "No"],
            "assets" => EvaluationToolAsset::where("mime", "LIKE", 'image/%')->get()->take(3)->pluck("id"),

        ], "Bildbewertung", "Bewertung von Bildern")->create();

        $yayNayId = EvaluationToolSurveyElement::all()->last()->id;

        EvaluationToolSurveyElementFactory::times(1)->emoji([
            "question" => [
                "de" => "Wie zufrieden bist Du mit der barrierefreien Nutzung des Tools?",
                "en" => "How happy are you with the accessibility of this tool?",
            ],
            "emojis" => [
                ["type" => "😁", "meaning" => "satisfied"],
                ["type" => "🤔", "meaning" => "neutral"],
                ["type" => "😥", "meaning" => "not satisfied"],
            ],
        ], "Öffnungszeiten", "Zufriedenheit Öffnungszeiten")->create();

        $emojiId = EvaluationToolSurveyElement::all()->last()->id;

        // $subElementIds = [];
        // $i = 0;
        // while ($i < 3) {
        //     EvaluationToolSurveyElementFactory::times(1)->starRating([
        //         "question" => [
        //             "de" => "Eine auf deutsch formulierte Frage",
        //             "en" => "A question presented in English",
        //         ],
        //         "allowHalfSteps" => false,
        //         "numberOfStars" => 5,
        //         "meaningLowestValue" => "very unhappy",
        //         "meaningHighestValue" => "very happy",
        //         "lowestValueLabel" => ["de" => "sehr unglücklich", "en" => "very unhappy"],
        //         "middleValueLabel" => ["de" => "neutral", "en" => "neutral"],
        //         "highestValueLabel" => ["de" => "sehr glücklich", "en" => "very happy"],
        //     ], "Sterne-Bewertung", "Von sehr unglücklich bis sehr glücklich")->create();
        //     $i++;
        //     $subElementIds[] = EvaluationToolSurveyElement::all()->last()->id;
        // }
        // EvaluationToolSurveyElementFactory::times(1)->video([
        //     "videoAssetId" => EvaluationToolAsset::find(1)->id
        // ], "Syncing Video")->create();
        // $videoId = EvaluationToolSurveyElement::all()->last()->id;


        EvaluationToolSurveyElementFactory::times(1)->textInput([
            "question" => [
                "de" => "Welches Feature hat Dir am besten gefallen?",
                "en" => "What feature did you like the most?",
            ],
        ], "Lieblingsfeature", "offene Frage zum Lieblingsfeature")->create();

        $textInputId = EvaluationToolSurveyElement::all()->last()->id;

        EvaluationToolSurveyElementFactory::times(1)->voiceInput([
            "question" => [
                "de" => "Hast Du noch Verbesserungsvorschläge?",
                "en" => "Do you have any suggestions for improvement?",
            ],
        ], "Verbesserungsvorschläge", "Verbesserungsvorschläge in Sprachform")->create();

        $voiceInputId = EvaluationToolSurveyElement::all()->last()->id;

        EvaluationToolSurveyFactory::times(1)->withName("Umfrage mit allen Elementtypen", "Umfrage mit allen Elementtypen in verschiedenen Konfigurationen")
            ->create();
        $surveyId = EvaluationToolSurvey::all()->last()->id;

        EvaluationToolSurveyStepFactory::times(1)->withData("Einleitung", $introId, $surveyId, $multipleChoiceId)->create();
        EvaluationToolSurveyStepFactory::times(1)->withData("Einfach-Auswahl", $multipleChoiceId, $surveyId, $starRatingId)->create();
        EvaluationToolSurveyStepFactory::times(1)->withData("Registrierungsprozess", $starRatingId, $surveyId, $yayNayId)->create();
        EvaluationToolSurveyStepFactory::times(1)->withData("Bildbewertung", $yayNayId, $surveyId, $emojiId)->create();
        EvaluationToolSurveyStepFactory::times(1)->withData("Barrierefreiheit", $emojiId, $surveyId, $textInputId)->create();
        // EvaluationToolSurveyStepFactory::times(1)->withData("Video", $videoId, $surveyId, $textInputId, $timebasedSteps)->create();
        EvaluationToolSurveyStepFactory::times(1)->withData("Lieblingsfeature", $textInputId, $surveyId, $voiceInputId)->create();
        EvaluationToolSurveyStepFactory::times(1)->withData("Verbesserungsvorschläge", $voiceInputId, $surveyId)->create();

    }
}
