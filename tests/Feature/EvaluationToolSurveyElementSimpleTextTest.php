<?php

namespace Tests\Feature;

use Faker\Factory;
use Tests\TestCase;
use Twoavy\EvaluationTool\Helpers\TestHelper;

class EvaluationToolSurveyElementSimpleTextTest extends TestCase
{
    private function validData(): array
    {
        return [
            "name" => "Test",
            "surveyElementType" => "simpleText",
            "params" => [
                "text" =>
                [
                    "de" => "Text DE",
                    "en" => "Text EN",
                ],
            ],
        ];
    }

    public function test_create_survey_element_simple_text_no_text()
    {
        $headers = TestHelper::getAuthHeader();
        $data = $this->validData();
        unset($data["params"]["text"]);

        $response = $this->post('/api/evaluation-tool/survey-elements', $data, $headers);
        $response->assertStatus(422);
    }

    public function test_create_survey_element_simple_text_wrong_language_key()
    {
        $headers = TestHelper::getAuthHeader();
        $data = $this->validData();
        $data["params"]["text"]["xx"] = "test";

        $response = $this->post('/api/evaluation-tool/survey-elements', $data, $headers);
        $response->assertStatus(422);
    }

    public function test_create_survey_element_simple_text_too_short()
    {
        $headers = TestHelper::getAuthHeader();
        $data = $this->validData();
        $data["params"]["text"]["de"] = "";

        $response = $this->post('/api/evaluation-tool/survey-elements', $data, $headers);
        $response->assertStatus(422);
    }

    public function test_create_survey_element_simple_text_too_long()
    {
        $headers = TestHelper::getAuthHeader();
        $faker = Factory::create();
        $data = $this->validData();
        $data["params"]["text"]["de"] = $faker->words(500, true);

        $response = $this->post('/api/evaluation-tool/survey-elements', $data, $headers);
        $response->assertStatus(422);
    }
}
