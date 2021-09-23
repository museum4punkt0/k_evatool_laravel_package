<?php

namespace Tests\Feature;

use Faker\Factory;
use Tests\TestCase;
use Twoavy\EvaluationTool\Helpers\TestHelper;

class EvaluationToolSurveyElementYayNayTest extends TestCase
{
    private function validData(): array
    {
        return [
            "name" => "Test",
            "surveyElementType" => "yayNay",
            "params" => [
                "question" =>
                [
                    "de" => "Question DE",
                    "en" => "Question EN",
                ],
            ],
        ];
    }

    public function test_create_survey_element_yay_nay_no_question()
    {
        $headers = TestHelper::getAuthHeader();
        $data = $this->validData();
        unset($data["params"]["question"]);

        $response = $this->post('/api/evaluation-tool/survey-elements', $data, $headers);
        $response->assertStatus(422);
    }

    public function test_create_survey_element_yay_nay_wrong_language_key()
    {
        $headers = TestHelper::getAuthHeader();
        $data = $this->validData();
        $data["params"]["question"]["xx"] = "test";

        $response = $this->post('/api/evaluation-tool/survey-elements', $data, $headers);
        $response->assertStatus(422);
    }

    public function test_create_survey_element_yay_nay_question_too_short()
    {
        $headers = TestHelper::getAuthHeader();
        $data = $this->validData();
        $data["params"]["question"]["de"] = "";

        $response = $this->post('/api/evaluation-tool/survey-elements', $data, $headers);
        $response->assertStatus(422);
    }

    public function test_create_survey_element_yay_nay_question_too_long()
    {
        $headers = TestHelper::getAuthHeader();
        $faker = Factory::create();
        $data = $this->validData();
        $data["params"]["question"]["de"] = $faker->words(500, true);

        $response = $this->post('/api/evaluation-tool/survey-elements', $data, $headers);
        $response->assertStatus(422);
    }
}
