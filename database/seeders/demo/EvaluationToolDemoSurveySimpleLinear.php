<?php

namespace Twoavy\EvaluationTool\Seeders\demo;

use Illuminate\Database\Seeder;
use Twoavy\EvaluationTool\Factories\EvaluationToolSurveyElementFactory;
use Twoavy\EvaluationTool\Factories\EvaluationToolSurveyFactory;
use Twoavy\EvaluationTool\Factories\EvaluationToolSurveyStepFactory;
use Twoavy\EvaluationTool\Http\Controllers\EvaluationToolAssetController;
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
            ]
        ], "Einleitungstext", "Keine Antwortmöglichkeit")->create();

        $introId = EvaluationToolSurveyElement::all()->last()->id;

        EvaluationToolSurveyElementFactory::times(1)->multipleChoice([
            "options"       => [
                [
                    "de" => "Option 1",
                    "en" => "Option 1",
                ],
                [
                    "de" => "Option 2",
                    "en" => "Option 2",
                ]
            ],
            "minSelectable" => 1,
            "maxSelectable" => 1,
        ], "Mulitple Choice", "Nur eine Antwort möglich")->create();

        $multipleChoiceId = EvaluationToolSurveyElement::all()->last()->id;

        EvaluationToolSurveyFactory::times(1)->withName("Einfache Umfrage", "Lineare Abfolge, ohne konditionale Elemente")->create();
        $surveyId = EvaluationToolSurvey::get()->last()->id;

        EvaluationToolSurveyStepFactory::times(1)->withData("Einleitung", $introId, $surveyId)->create();
        EvaluationToolSurveyStepFactory::times(1)->withData("Auswahl", $multipleChoiceId, $surveyId)->create();


    }
}
