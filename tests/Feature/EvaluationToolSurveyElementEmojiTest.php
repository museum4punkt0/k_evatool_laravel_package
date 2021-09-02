<?php

namespace Tests\Feature;

use Faker\Factory;
use Tests\TestCase;
use Twoavy\EvaluationTool\Helpers\EvaluationToolHelper;
use Twoavy\EvaluationTool\Models\EvaluationToolSurveyElement;
use Twoavy\EvaluationTool\SurveyElementTypes\EvaluationToolSurveyElementTypeEmoji;

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
        $data = $this->validData();

        $response = $this->post('/api/evaluation-tool/survey-elements', $data);
        try {
            $response->assertStatus(200);
        } catch (\Exception $e) {
            $this->fail($response->getContent());
        }
    }

    public function test_create_survey_element_emoji_no_emoji()
    {
        $data = $this->validData();
        unset($data["params"]["emojis"]);

        $response = $this->post('/api/evaluation-tool/survey-elements', $data);
        try {
            $response->assertStatus(422);
        } catch (\Exception $e) {
            $this->fail($response->getContent());
        }
    }

    public function test_create_survey_element_emoji_type_too_short()
    {
        $data                                = $this->validData();
        $data["params"]["emojis"][0]["type"] = "";

        $response = $this->post('/api/evaluation-tool/survey-elements', $data);
        try {
            $response->assertStatus(422);
        } catch (\Exception $e) {
            $this->fail($response->getContent());
        }
    }

    public function test_create_survey_element_emoji_type_too_long()
    {
        $data                                = $this->validData();
        $data["params"]["emojis"][0]["type"] = "22";

        $response = $this->post('/api/evaluation-tool/survey-elements', $data);
        try {
            $response->assertStatus(422);
        } catch (\Exception $e) {
            $this->fail($response->getContent());
        }
    }
}
