<?php

namespace Tests\Feature;

use Faker\Factory;
use Tests\TestCase;
use Twoavy\EvaluationTool\Helpers\EvaluationToolHelper;
use Twoavy\EvaluationTool\Models\EvaluationToolSurveyElement;

class EvaluationToolSurveyElementSimpleTextTest extends TestCase
{
    private function validData(): array
    {
        return [
            "name"              => "Test",
            "surveyElementType" => "simpleText",
            "params"            => [
                "text" =>
                    [
                        "de" => "Option 1 DE",
                        "en" => "Option 1 EN",
                    ]
            ],
        ];
    }

    public function test_create_survey_element_simple_text_no_text()
    {
        $data = $this->validData();
        unset($data["params"]["text"]);

        $response = $this->post('/api/evaluation-tool/survey-elements', $data);
        try {
            $response->assertStatus(422);
        } catch (\Exception $e) {
            $this->fail($response->getContent());
        }
    }

    public function test_create_survey_element_simple_text_wrong_language_key()
    {
        $data                         = $this->validData();
        $data["params"]["text"]["xx"] = "test";

        $response = $this->post('/api/evaluation-tool/survey-elements', $data);
        try {
            $response->assertStatus(422);
        } catch (\Exception $e) {
            $this->fail($response->getContent());
        }
    }

    public function test_create_survey_element_simple_text_too_short()
    {
        $data                         = $this->validData();
        $data["params"]["text"]["de"] = "";

        $response = $this->post('/api/evaluation-tool/survey-elements', $data);
        try {
            $response->assertStatus(422);
        } catch (\Exception $e) {
            $this->fail($response->getContent());
        }
    }

    public function test_create_survey_element_simple_text_too_long()
    {
        $faker                        = Factory::create();
        $data                         = $this->validData();
        $data["params"]["text"]["de"] = $faker->words(500, true);

        $response = $this->post('/api/evaluation-tool/survey-elements', $data);
        try {
            $response->assertStatus(422);
        } catch (\Exception $e) {
            $this->fail($response->getContent());
        }
    }
}
