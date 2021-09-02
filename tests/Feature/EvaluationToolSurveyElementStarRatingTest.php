<?php

namespace Tests\Feature;

use Faker\Factory;
use Tests\TestCase;
use Twoavy\EvaluationTool\Helpers\EvaluationToolHelper;
use Twoavy\EvaluationTool\Models\EvaluationToolSurveyElement;

class EvaluationToolSurveyElementStarRatingTest extends TestCase
{
    private function validData(): array
    {
        return [
            "name"              => "Test",
            "surveyElementType" => "starRating",
            "params"            => [
                "question"       =>
                    [
                        "de" => "Option 1 DE",
                        "en" => "Option 1 EN",
                    ],
                "numberOfSteps"  => 3,
                "allowHalfSteps" => true
            ],
        ];
    }

    public function test_create_survey_element_simple_text_valid()
    {
        $data = $this->validData();

        $response = $this->post('/api/evaluation-tool/survey-elements', $data);
        try {
            $response->assertStatus(200);
        } catch (\Exception $e) {
            $this->fail($response->getContent());
        }
    }

    public function test_create_survey_element_simple_text_no_question()
    {
        $data = $this->validData();
        unset($data["params"]["question"]);

        $response = $this->post('/api/evaluation-tool/survey-elements', $data);
        try {
            $response->assertStatus(422);
        } catch (\Exception $e) {
            $this->fail($response->getContent());
        }
    }

    public function test_create_survey_element_simple_text_wrong_language_key()
    {
        $data                             = $this->validData();
        $data["params"]["question"]["xx"] = "test";

        $response = $this->post('/api/evaluation-tool/survey-elements', $data);
        try {
            $response->assertStatus(422);
        } catch (\Exception $e) {
            $this->fail($response->getContent());
        }
    }

    public function test_create_survey_element_simple_text_too_short()
    {
        $data                             = $this->validData();
        $data["params"]["question"]["de"] = "";

        $response = $this->post('/api/evaluation-tool/survey-elements', $data);
        try {
            $response->assertStatus(422);
        } catch (\Exception $e) {
            $this->fail($response->getContent());
        }
    }

    public function test_create_survey_element_simple_text_too_long()
    {
        $faker                            = Factory::create();
        $data                             = $this->validData();
        $data["params"]["question"]["de"] = $faker->words(500, true);

        $response = $this->post('/api/evaluation-tool/survey-elements', $data);
        try {
            $response->assertStatus(422);
        } catch (\Exception $e) {
            $this->fail($response->getContent());
        }
    }

    public function test_create_survey_element_simple_text_allow_half_step_wrong_type_string()
    {
        $data                             = $this->validData();
        $data["params"]["allowHalfSteps"] = "string";

        $response = $this->post('/api/evaluation-tool/survey-elements', $data);
        try {
            $response->assertStatus(422);
        } catch (\Exception $e) {
            $this->fail($response->getContent());
        }
    }

    public function test_create_survey_element_simple_text_allow_half_step_wrong_type_number()
    {
        $data                             = $this->validData();
        $data["params"]["allowHalfSteps"] = 2;

        $response = $this->post('/api/evaluation-tool/survey-elements', $data);
        try {
            $response->assertStatus(422);
        } catch (\Exception $e) {
            $this->fail($response->getContent());
        }
    }

    public function test_create_survey_element_simple_text_allow_number_of_steps_missing()
    {
        $data = $this->validData();
        unset($data["params"]["numberOfSteps"]);

        $response = $this->post('/api/evaluation-tool/survey-elements', $data);
        try {
            $response->assertStatus(422);
        } catch (\Exception $e) {
            $this->fail($response->getContent());
        }
    }

    public function test_create_survey_element_simple_text_allow_number_of_steps_wrong_type_string()
    {
        $data                            = $this->validData();
        $data["params"]["numberOfSteps"] = "string";

        $response = $this->post('/api/evaluation-tool/survey-elements', $data);
        try {
            $response->assertStatus(422);
        } catch (\Exception $e) {
            $this->fail($response->getContent());
        }
    }

    public function test_create_survey_element_simple_text_allow_number_of_steps_too_low()
    {
        $data                            = $this->validData();
        $data["params"]["numberOfSteps"] = 1;

        $response = $this->post('/api/evaluation-tool/survey-elements', $data);
        try {
            $response->assertStatus(422);
        } catch (\Exception $e) {
            $this->fail($response->getContent());
        }
    }

    public function test_create_survey_element_simple_text_allow_number_of_steps_too_high()
    {
        $data                            = $this->validData();
        $data["params"]["numberOfSteps"] = 11;

        $response = $this->post('/api/evaluation-tool/survey-elements', $data);
        try {
            $response->assertStatus(422);
        } catch (\Exception $e) {
            $this->fail($response->getContent());
        }
    }
}
