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

class EvaluationToolDemoSurveySimpleVideo extends Seeder
{
    /**
     * @return void
     */
    public function run()
    {
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
                "meaningLowestValue"  => "very unhappy",
                "meaningHighestValue" => "very happy"
            ], "Sterne-Bewertung", "Von sehr unglücklich bis sehr glücklich")->create();
            $i++;
            $subElementIds[] = EvaluationToolSurveyElement::all()->last()->id;
        }

        EvaluationToolSurveyElementFactory::times(1)->video([
            "videoAssetId" => EvaluationToolAsset::find(1)->id
        ], "Syncing Video")->create();
        $videoId = EvaluationToolSurveyElement::all()->last()->id;

        EvaluationToolSurveyFactory::times(1)->withName("Einfaches Video", "Video mit zeitbasierten Unterschritten")->create();
        $surveyId = EvaluationToolSurvey::get()->last()->id;

        $timebasedSteps = [];
        foreach ($subElementIds as $s => $subElementId) {
            EvaluationToolSurveyStepFactory::times(1)->withData("Bewertung " . ($s + 1), $subElementId, $surveyId)->create();
            $timebasedSteps[] = [
                "uuid"                => Uuid::uuid4(),
                "stepId"              => $subElementId,
                "timecode"            => "00:00:" . sprintf('%02d', (($s + 1) * 3 + pow($s, 2))) . ":00",
                "stopsVideo"          => true,
                "description"         => "Beschreibung " . ($s + 1),
                "displayTime"         => 2,
                "allowChangingAnswer" => false
            ];
        }

        EvaluationToolSurveyStepFactory::times(1)->withData("Video", $videoId, $surveyId, null, $timebasedSteps)->create();
    }
}