<?php

namespace Tests\Feature;

use Tests\TestCase;

use Twoavy\EvaluationTool\Helpers\TestHelper;

class EvaluationToolSurveyElementEmojiTest extends TestCase
{
    private function validData(): array
    {
        return [
            "name"              => "Test",
            "surveyElementType" => "emoji",
            "params"            => [
                "emojis" => [
                    [
                        "type"    => "😊",
                        "meaning" => "great",
                    ],
                    [
                        "type"    => "😠",
                        "meaning" => "angry",
                    ],
                    [
                        "type"    => "😎",
                        "meaning" => "cool",
                    ]
                ]
            ],
        ];
    }

    public function test_create_survey_element_emoji_valid()
    {
        $headers = TestHelper::getAuthHeader();

        $data = $this->validData();

        $response = $this->post('/api/evaluation-tool/survey-elements', $data, $headers);
        $response->assertStatus(200);

    }

    public function test_create_survey_element_emoji_no_emoji()
    {
        $headers = TestHelper::getAuthHeader();

        $data = $this->validData();
        unset($data["params"]["emojis"]);

        $response = $this->post('/api/evaluation-tool/survey-elements', $data, $headers);
        $response->assertStatus(422);

    }

    public function test_create_survey_element_emoji_type_too_short()
    {
        $headers = TestHelper::getAuthHeader();

        $data                                = $this->validData();
        $data["params"]["emojis"][0]["type"] = "";

        $response = $this->post('/api/evaluation-tool/survey-elements', $data, $headers);
        $response->assertStatus(422);

    }

    public function test_create_survey_element_emoji_type_too_long()
    {
        $headers = TestHelper::getAuthHeader();

        $data                                = $this->validData();
        $data["params"]["emojis"][0]["type"] = "22";

        $response = $this->post('/api/evaluation-tool/survey-elements', $data, $headers);
        $response->assertStatus(422);

    }
}
