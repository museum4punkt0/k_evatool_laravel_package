<?php

namespace Tests\Feature;

use Faker\Factory;
use Tests\TestCase;
use Twoavy\EvaluationTool\Helpers\TestHelper;

class EvaluationToolSurveyElementStarRatingTest extends TestCase
{
    private function validData(): array
    {
        return [
            "name" => "Test",
            "surveyElementType" => "starRating",
            "params" => [
                "question" =>
                [
                    "de" => "Option 1 DE",
                    "en" => "Option 1 EN",
                ],
                "numberOfStars" => 3,
                "allowHalfSteps" => true,
            ],
        ];
    }

    public function test_create_survey_element_simple_text_valid()
    {
        $headers = TestHelper::getAuthHeader();
        $data = $this->validData();

        $response = $this->post('/api/evaluation-tool/survey-elements', $data, $headers);
        $response->assertStatus(200);
    }

    public function test_create_survey_element_simple_text_no_question()
    {
        $headers = TestHelper::getAuthHeader();
        $data = $this->validData();
        unset($data["params"]["question"]);

        $response = $this->post('/api/evaluation-tool/survey-elements', $data, $headers);
        $response->assertStatus(422);
    }

    public function test_create_survey_element_simple_text_wrong_language_key()
    {
        $headers = TestHelper::getAuthHeader();
        $data = $this->validData();
        $data["params"]["question"]["xx"] = "test";

        $response = $this->post('/api/evaluation-tool/survey-elements', $data, $headers);
        $response->assertStatus(422);
    }

    public function test_create_survey_element_simple_text_too_short()
    {
        $headers = TestHelper::getAuthHeader();
        $data = $this->validData();
        $data["params"]["question"]["de"] = "";

        $response = $this->post('/api/evaluation-tool/survey-elements', $data, $headers);
        $response->assertStatus(422);
    }

    public function test_create_survey_element_simple_text_too_long()
    {
        $headers = TestHelper::getAuthHeader();
        $faker = Factory::create();
        $data = $this->validData();
        $data["params"]["question"]["de"] = $faker->words(500, true);

        $response = $this->post('/api/evaluation-tool/survey-elements', $data, $headers);
        $response->assertStatus(422);
    }

    public function test_create_survey_element_simple_text_allow_half_step_wrong_type_string()
    {
        $headers = TestHelper::getAuthHeader();
        $data = $this->validData();
        $data["params"]["allowHalfSteps"] = "string";

        $response = $this->post('/api/evaluation-tool/survey-elements', $data, $headers);
        $response->assertStatus(422);
    }

    public function test_create_survey_element_simple_text_allow_half_step_wrong_type_number()
    {
        $headers = TestHelper::getAuthHeader();
        $data = $this->validData();
        $data["params"]["allowHalfSteps"] = 2;

        $response = $this->post('/api/evaluation-tool/survey-elements', $data, $headers);
        $response->assertStatus(422);
    }

    public function test_create_survey_element_simple_text_allow_number_of_steps_missing()
    {
        $headers = TestHelper::getAuthHeader();
        $data = $this->validData();
        unset($data["params"]["numberOfStars"]);

        $response = $this->post('/api/evaluation-tool/survey-elements', $data, $headers);
        $response->assertStatus(422);
    }

    public function test_create_survey_element_simple_text_allow_number_of_steps_wrong_type_string()
    {
        $headers = TestHelper::getAuthHeader();
        $data = $this->validData();
        $data["params"]["numberOfStars"] = "string";

        $response = $this->post('/api/evaluation-tool/survey-elements', $data, $headers);
        $response->assertStatus(422);
    }

    public function test_create_survey_element_simple_text_allow_number_of_steps_too_low()
    {
        $headers = TestHelper::getAuthHeader();
        $data = $this->validData();
        $data["params"]["numberOfStars"] = 1;

        $response = $this->post('/api/evaluation-tool/survey-elements', $data, $headers);
        $response->assertStatus(422);
    }

    public function test_create_survey_element_simple_text_allow_number_of_steps_too_high()
    {
        $headers = TestHelper::getAuthHeader();
        $data = $this->validData();
        $data["params"]["numberOfStars"] = 11;

        $response = $this->post('/api/evaluation-tool/survey-elements', $data, $headers);
        $response->assertStatus(422);
    }
}
