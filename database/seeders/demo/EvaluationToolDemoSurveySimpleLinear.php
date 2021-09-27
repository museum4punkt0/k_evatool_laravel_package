<?php

namespace Twoavy\EvaluationTool\Seeders\demo;

use Illuminate\Database\Seeder;
use Twoavy\EvaluationTool\Factories\EvaluationToolSurveyElementFactory;
use Twoavy\EvaluationTool\Factories\EvaluationToolSurveyFactory;
use Twoavy\EvaluationTool\Factories\EvaluationToolSurveyStepFactory;
use Twoavy\EvaluationTool\Models\EvaluationToolSurvey;
use Twoavy\EvaluationTool\Models\EvaluationToolSurveyElement;

class EvaluationToolDemoSurveySimpleLinear extends Seeder
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
        ], "Einleitungstext", "Keine Antwortmöglichkeit")->create();

        $introId = EvaluationToolSurveyElement::all()->last()->id;

        EvaluationToolSurveyElementFactory::times(1)->multipleChoice([
            "question" => [
                "de" => "Eine auf deutsch formulierte Frage",
                "en" => "A question presented in English",
                "fr" => "une question en francais",
            ],
            "options" => [
                [

                    'value' => 'option 1',
                    'labels' => [
                        "de" => "Option 1",
                        "en" => "Option 1",
                        "fr" => "Option 1",
                    ],
                ],
                [
                    'value' => 'option 2',
                    'labels' => [
                        "de" => "Option 2",
                        "en" => "Option 2",
                        "fr" => "Option 2",
                    ],
                ],
            ],
            "minSelectable" => 1,
            "maxSelectable" => 1,
        ], "Mulitple Choice", "Nur eine Antwort möglich")->create();

        $multipleChoiceId = EvaluationToolSurveyElement::all()->last()->id;

        EvaluationToolSurveyElementFactory::times(1)->starRating([
            "question" => [
                "de" => "Eine auf deutsch formulierte Frage",
                "en" => "A question presented in English",
            ],
            "allowHalfSteps" => false,
            "numberOfStars" => 5,
            "meaningLowestValue" => "very unhappy",
            "meaningHighestValue" => "very happy",
        ], "Sterne-Bewertung", "Von sehr unglücklich bis sehr glücklich")->create();

        $starRatingId = EvaluationToolSurveyElement::all()->last()->id;

        EvaluationToolSurveyFactory::times(1)->withName("Einfache Umfrage", "Lineare Abfolge, ohne konditionale Elemente")->create();
        $surveyId = EvaluationToolSurvey::get()->last()->id;

        EvaluationToolSurveyStepFactory::times(1)->withData("Einleitung", $introId, $surveyId, $multipleChoiceId)->create();
        EvaluationToolSurveyStepFactory::times(1)->withData("Einfach-Auswahl", $multipleChoiceId, $surveyId, $starRatingId)->create();
        EvaluationToolSurveyStepFactory::times(1)->withData("Bewertung", $starRatingId, $surveyId)->create();

    }
}
