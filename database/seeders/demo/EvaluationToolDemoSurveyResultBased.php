<?php

namespace Twoavy\EvaluationTool\Seeders\demo;

use Illuminate\Database\Seeder;
use Twoavy\EvaluationTool\Factories\EvaluationToolSurveyElementFactory;
use Twoavy\EvaluationTool\Factories\EvaluationToolSurveyFactory;
use Twoavy\EvaluationTool\Factories\EvaluationToolSurveyStepFactory;
use Twoavy\EvaluationTool\Models\EvaluationToolSurvey;
use Twoavy\EvaluationTool\Models\EvaluationToolSurveyElement;
use Twoavy\EvaluationTool\Models\EvaluationToolSurveyLanguage;
use Twoavy\EvaluationTool\Seeders\EvaluationToolSeeder;

class EvaluationToolDemoSurveyResultBased extends Seeder
{
    /**
     * @return void
     */
    public function run()
    {
        EvaluationToolSurveyElementFactory::times(1)->multipleChoice([
            "question" => [
                "de" => "Eine auf deutsch formulierte Frage",
                "en" => "A question presented in English"
            ],
            "options" => [
                [

                    'value' => 'option_1',
                    'labels' => [
                        "de" => "Option 1",
                        "en" => "Option 1"
                    ],
                ],
                [
                    'value' => 'option2',
                    'labels' => [
                        "de" => "Option 2",
                        "en" => "Option 2"
                    ],
                ],
            ],
            "minSelectable" => 1,
            "maxSelectable" => 1,
        ], "Mulitple Choice", "Nur eine Antwort möglich")->create();
        $multipleChoiceId = EvaluationToolSurveyElement::all()->last()->id;

        EvaluationToolSurveyElementFactory::times(1)->multipleChoice([
            "question" => [
                "de" => "Eine weitere auf deutsch formulierte Frage",
                "en" => "Another question presented in English"
            ],
            "options" => [
                [
                    'value' => 'option_1',
                    'labels' => [
                        "de" => "Option 1",
                        "en" => "Option 1"
                    ],
                ],
                [
                    'value' => 'option2',
                    'labels' => [
                        "de" => "Option 2",
                        "en" => "Option 2"
                    ],
                ],
                [
                    'value' => 'option3',
                    'labels' => [
                        "de" => "Option 3",
                        "en" => "Option 3"
                    ],
                ],
            ],
            "minSelectable" => 1,
            "maxSelectable" => 1,
        ], "Another Multiple Choice", "Nur eine Antwort möglich")->create();
        $anotherMultipleChoiceId = EvaluationToolSurveyElement::all()->last()->id;

        EvaluationToolSurveyElementFactory::times(1)->starRating([
            "question" => [
                "de" => "Eine auf deutsch formulierte Frage",
                "en" => "A question presented in English",
            ],
            "allowHalfSteps" => false,
            "numberOfStars" => 5,
            "meaningLowestValue" => "very_unhappy",
            "meaningHighestValue" => "very_happy",
            "lowestValueLabel" => ["de" => "sehr unglücklich", "en" => "very unhappy"],
            "middleValueLabel" => ["de" => "neutral", "en" => "neutral"],
            "highestValueLabel" => ["de" => "sehr glücklich", "en" => "very happy"],
        ], "Sterne-Bewertung", "Von sehr unglücklich bis sehr glücklich")->create();

        $starRatingId = EvaluationToolSurveyElement::all()->last()->id;

        EvaluationToolSurveyElementFactory::times(1)->binary([
            "question" => [
                "de" => "Gefällt Dir das Tool?",
                "en" => "Do you like the tool?",
            ],
            "trueValue" => "ja",
            "falseValue" => "nein",
            "trueLabel" => ["de" => "ja", "en" => "yes"],
            "falseLabel" => ["de" => "nein", "en" => "no"],
        ], "Binary Frage", "ja oder nein")->create();

        $binaryId = EvaluationToolSurveyElement::all()->last()->id;

        EvaluationToolSurveyFactory::times(1)->withName("Umfrage mit ergebnisbasierten Folgeschritten", "Komplexere Anordnung mit Weichenstellungen.")
            ->create();

        // set languages
        $survey = EvaluationToolSurvey::get()->last();
        $survey->languages()->sync(EvaluationToolSurveyLanguage::all()->random(rand(1, EvaluationToolSurveyLanguage::all()->count())));

        // set survey id
        $surveyId = $survey->id;

        EvaluationToolSurveyStepFactory::times(1)->withData("Einfach-Auswahl", $multipleChoiceId, $surveyId, null, null, true)->create();
        $multipleChoiceStep = EvaluationToolSeeder::getLatestStep();

        EvaluationToolSurveyStepFactory::times(1)->withData("Bewertung", $starRatingId, $surveyId)->create();
        $multipleChoiceStep->next_step_id = EvaluationToolSeeder::getLatestStep()->id;
        $multipleChoiceStep->save();
        $starRatingStep = EvaluationToolSeeder::getLatestStep();

        EvaluationToolSurveyStepFactory::times(1)->withData("Binary", $binaryId, $surveyId)->create();
        // $starRatingStep->next_step_id = EvaluationToolSeeder::getLatestStep()->id;
        $starRatingStep->save();
        $binaryStep = EvaluationToolSeeder::getLatestStep();

        $conditionalSteps = [];
        for ($i = 1; $i <= 7; $i++) {
            EvaluationToolSurveyStepFactory::times(1)->withData("Sub-Bewertung " . $i, $starRatingId, $surveyId)->create();
            $conditionalSteps["starRating" . $i] = EvaluationToolSeeder::getLatestStep();
        }

        $starRatingStep->result_based_next_steps = [
            [
                "start" => 1,
                "end" => 1,
                "stepId" => $conditionalSteps["starRating1"]->id,
            ],
            [
                "start" => 4,
                "end" => 5,
                "stepId" => $conditionalSteps["starRating2"]->id,
            ],
            [
                "start" => 2,
                "end" => 3,
                "stepId" => $conditionalSteps["starRating3"]->id,
            ],
        ];

        $starRatingStep->save();

        $conditionalSteps["starRating1"]->next_step_id = $binaryStep->id;
        $conditionalSteps["starRating1"]->save();
        $conditionalSteps["starRating2"]->next_step_id = $binaryStep->id;
        $conditionalSteps["starRating2"]->save();
        $conditionalSteps["starRating3"]->next_step_id = $binaryStep->id;
        $conditionalSteps["starRating3"]->save();

        $binaryStep->result_based_next_steps = [
            "trueNextStep" => [
                "stepId" => $conditionalSteps["starRating4"]->id,
            ],
            "falseNextStep" => [
                "stepId" => $conditionalSteps["starRating5"]->id,
            ],
        ];

        $binaryStep->save();

        EvaluationToolSurveyStepFactory::times(1)->withData("andere Einfach-Auswahl", $anotherMultipleChoiceId, $surveyId, null, null, false)->create();
        $anotherMultipleChoiceStep = EvaluationToolSeeder::getLatestStep();

        $conditionalSteps["starRating4"]->next_step_id = $anotherMultipleChoiceStep->id;
        $conditionalSteps["starRating4"]->save();
        $conditionalSteps["starRating5"]->next_step_id = $anotherMultipleChoiceStep->id;
        $conditionalSteps["starRating5"]->save();

        $anotherMultipleChoiceStep->next_step_id = $conditionalSteps["starRating7"]->id;
        $anotherMultipleChoiceStep->result_based_next_steps = [
            [
                "value" => "option1",
                "stepId" => $conditionalSteps["starRating6"]->id,
            ],
        ];
        $anotherMultipleChoiceStep->save();

        // $anotherMultipleChoiceStep->result_based_next_steps = array(

        //     [
        //         "value" => "option1",
        //         "stepId" => $conditionalSteps["starRating6"]->id,
        //         ]
        //     );
        // $anotherMultipleChoiceStep->next_step_id = $conditionalSteps["starRating7"]->id;
    }
}
