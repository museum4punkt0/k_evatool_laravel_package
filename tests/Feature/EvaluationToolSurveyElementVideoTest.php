<?php

namespace Tests\Feature;

use Tests\TestCase;
use Twoavy\EvaluationTool\Helpers\TestHelper;

class EvaluationToolSurveyElementVideoTest extends TestCase
{
    private function validData(): array
    {
        return [
            "name" => "Test",
            "surveyElementType" => "video",
            "params" => [
                "videoAssetId" => 1,
            ],
        ];
    }

    public function test_create_survey_element_video_valid()
    {
        $headers = TestHelper::getAuthHeader();
        $data = $this->validData();

        $response = $this->post('/api/evaluation-tool/survey-elements', $data, $headers);
        $response->assertStatus(200);
    }

    public function test_create_survey_element_video_no_video()
    {
        $headers = TestHelper::getAuthHeader();
        $data = $this->validData();
        unset($data["params"]["videoAssetId"]);

        $response = $this->post('/api/evaluation-tool/survey-elements', $data, $headers);
        $response->assertStatus(422);
    }
}
