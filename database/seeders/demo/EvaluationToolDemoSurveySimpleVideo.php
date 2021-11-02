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
use Twoavy\EvaluationTool\Models\EvaluationToolSurveyStep;
use Twoavy\EvaluationTool\Seeders\EvaluationToolSeeder;

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
                "meaningLowestValue"  => "very_unhappy",
                "meaningHighestValue" => "very_happy",
                "lowestValueLabel" => ["de" => "sehr unglücklich", "en" => "very unhappy"],
                "middleValueLabel" => ["de" => "neutral", "en" => "neutral"],
                "highestValueLabel" => ["de" => "sehr glücklich", "en" => "very happy"],
            ], "Sterne-Bewertung", "Von sehr unglücklich bis sehr glücklich")->create();
            $i++;
            $subElementIds[] = EvaluationToolSurveyElement::all()->last()->id;
        }

        EvaluationToolSurveyElementFactory::times(1)->video([
            "videoAssetId" => EvaluationToolAsset::where("filename", "eva_tool_demo_video.mp4")->first()->id
        ], "Syncing Video")->create();
        $videoId = EvaluationToolSurveyElement::all()->last()->id;

        EvaluationToolSurveyFactory::times(1)->withName("Einfaches Video", "Video mit zeitbasierten Unterschritten")->create();
        $surveyId = EvaluationToolSurvey::get()->last()->id;

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
                "allowChangingAnswer" => false
            ];
        }

        EvaluationToolSurveyStep::where('is_first_step', true)->where('survey_id',$surveyId )->update(['is_first_step'=>null]);
        EvaluationToolSurveyStepFactory::times(1)->withData("Video", $videoId, $surveyId, null, $timebasedSteps, true)->create();
    }
}
