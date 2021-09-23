<?php

namespace Tests\Feature;

use Faker\Factory;
use Tests\TestCase;
use Twoavy\EvaluationTool\Helpers\TestHelper;

class EvaluationToolSurveyElementBinaryTest extends TestCase
{
    private function validData(): array
    {
        return [
            "name" => "Test",
            "surveyElementType" => "binary",
            "params" => [
                "question" =>
                [
                    "de" => "Question DE",
                    "en" => "Question EN",
                ],
                "trueValue" => "",
            ],
        ];
    }

    public function test_create_survey_element_binary_no_question()
    {
        $headers = TestHelper::getAuthHeader();
        $data = $this->validData();
        unset($data["params"]["question"]);

        $response = $this->post('/api/evaluation-tool/survey-elements', $data, $headers);
        $response->assertStatus(422);
    }

    public function test_create_survey_element_binary_wrong_language_key()
    {
        $headers = TestHelper::getAuthHeader();
        $data = $this->validData();
        $data["params"]["question"]["xx"] = "test";

        $response = $this->post('/api/evaluation-tool/survey-elements', $data, $headers);
        $response->assertStatus(422);
    }

    public function test_create_survey_element_binary_question_too_short()
    {
        $headers = TestHelper::getAuthHeader();
        $data = $this->validData();
        $data["params"]["question"]["de"] = "";

        $response = $this->post('/api/evaluation-tool/survey-elements', $data, $headers);
        $response->assertStatus(422);
    }

    public function test_create_survey_element_binary_question_too_long()
    {
        $headers = TestHelper::getAuthHeader();
        $faker = Factory::create();
        $data = $this->validData();
        $data["params"]["question"]["de"] = $faker->words(500, true);

        $response = $this->post('/api/evaluation-tool/survey-elements', $data, $headers);
        $response->assertStatus(422);
    }

    public function test_create_survey_element_binary_true_value_too_short()
    {
        $headers = TestHelper::getAuthHeader();
        $data = $this->validData();
        $data["params"]["trueValue"] = "";

        $response = $this->post('/api/evaluation-tool/survey-elements', $data, $headers);
        $response->assertStatus(422);
    }

    public function test_create_survey_element_binary_true_value_too_long()
    {
        $headers = TestHelper::getAuthHeader();
        $faker = Factory::create();
        $data = $this->validData();
        $data["params"]["trueValue"] = $faker->words(50, true);

        $response = $this->post('/api/evaluation-tool/survey-elements', $data, $headers);
        $response->assertStatus(422);
    }

    public function test_create_survey_element_binary_false_value_too_short()
    {
        $headers = TestHelper::getAuthHeader();
        $data = $this->validData();
        $data["params"]["falseValue"] = "";

        $response = $this->post('/api/evaluation-tool/survey-elements', $data, $headers);
        $response->assertStatus(422);
    }

    public function test_create_survey_element_binary_false_value_too_long()
    {
        $headers = TestHelper::getAuthHeader();
        $faker = Factory::create();
        $data = $this->validData();
        $data["params"]["falseValue"] = $faker->words(50, true);

        $response = $this->post('/api/evaluation-tool/survey-elements', $data, $headers);
        $response->assertStatus(422);
    }
}
