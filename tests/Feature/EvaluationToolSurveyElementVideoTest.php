<?php

namespace Tests\Feature;

use Faker\Factory;
use Tests\TestCase;
use Twoavy\EvaluationTool\Helpers\EvaluationToolHelper;
use Twoavy\EvaluationTool\Models\EvaluationToolSurveyElement;

class EvaluationToolSurveyElementVideoTest extends TestCase
{
    private function validData(): array
    {
        return [
            "name"              => "Test",
            "surveyElementType" => "video",
            "params"            => [
                "videoAssetId"  => 1
            ],
        ];
    }

    public function test_create_survey_element_video_valid()
    {
        $data = $this->validData();

        $response = $this->post('/api/evaluation-tool/survey-elements', $data);
        try {
            $response->assertStatus(200);
        } catch (\Exception $e) {
            $this->fail($response->getContent());
        }
    }

    public function test_create_survey_element_video_no_video()
    {
        $data = $this->validData();
        unset($data["params"]["videoAssetId"]);

        $response = $this->post('/api/evaluation-tool/survey-elements', $data);
        try {
            $response->assertStatus(422);
        } catch (\Exception $e) {
            $this->fail($response->getContent());
        }
    }
}
