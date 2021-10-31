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

class EvaluationToolDemoSurveyResultBased extends Seeder
{
    /**
     * @return void
     */
    public function run()
    {
        EvaluationToolSurveyElementFactory::times(1)->multipleChoice([
            "question"      => [
                "de" => "Eine auf deutsch formulierte Frage",
                "en" => "A question presented in English",
                "fr" => "une question en francais",
            ],
            "options"       => [
                [

                    'value'  => 'option_1',
                    'labels' => [
                        "de" => "Option 1",
                        "en" => "Option 1",
                        "fr" => "Option 1",
                    ],
                ],
                [
                    'value'  => 'option2',
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

        $starRatingId = EvaluationToolSurveyElement::all()->last()->id;

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

        EvaluationToolSurveyFactory::times(1)->withName("Umfrage mit ergebnisbasierten Folgeschritten", "Komplexere Anordnung mit Weichenstellungen.")
            ->create();
        $surveyId = EvaluationToolSurvey::get()->last()->id;

        EvaluationToolSurveyStepFactory::times(1)->withData("Einfach-Auswahl", $multipleChoiceId, $surveyId, null, null, true)->create();
        $multipleChoiceStep = EvaluationToolSeeder::getLatestStep();

        EvaluationToolSurveyStepFactory::times(1)->withData("Bewertung", $starRatingId, $surveyId)->create();
        $multipleChoiceStep->next_step_id = EvaluationToolSeeder::getLatestStep()->id;
        $multipleChoiceStep->save();
        $starRatingStep = EvaluationToolSeeder::getLatestStep();

        EvaluationToolSurveyStepFactory::times(1)->withData("Binary", $binaryId, $surveyId)->create();
        $starRatingStep->next_step_id = EvaluationToolSeeder::getLatestStep()->id;
        $starRatingStep->save();
        $binaryStep = EvaluationToolSeeder::getLatestStep();

        $conditionalSteps = [];
        for ($i = 1; $i <= 10; $i++) {
            EvaluationToolSurveyStepFactory::times(1)->withData("Sub-Bewertung " . $i, $starRatingId, $surveyId)->create();
            $conditionalSteps["starRating" . $i] = EvaluationToolSeeder::getLatestStep();
        }

        $starRatingStep->result_based_next_steps = [
            [
                "start"  => 1,
                "end"    => 1,
                "stepId" => $conditionalSteps["starRating1"]->id
            ],
            [
                "start"  => 4,
                "end"    => 5,
                "stepId" => $conditionalSteps["starRating2"]->id
            ],
            [
                "start"  => 2,
                "end"    => 3,
                "stepId" => $conditionalSteps["starRating3"]->id
            ]
        ];

        $starRatingStep->save();

        $binaryStep->result_based_next_steps = [
            "trueNextStep"  => [
                "stepId" => $conditionalSteps["starRating4"]->id
            ],
            "falseNextStep" => [
                "stepId" => $conditionalSteps["starRating5"]->id
            ]
        ];

        $binaryStep->save();
    }
}
