<?php

namespace Tests\Feature;

use Tests\TestCase;

class EvaluationToolSurveyElementMultipleChoiceTest extends TestCase
{
    private function validData(): array
    {
        return [
            "name" => "Test",
            "params" => [
                "min_elements" => 2,
                "max_elements" => 2,
                "options" => [
                    [
                        "de" => "Option 1 DE",
                        "en" => "Option 1 EN",
                    ],
                    [
                        "de" => "Option 2 DE",
                        "en" => "Option 2 EN",
                    ],
                ],
            ],
        ];
    }

    public function test_create_survey_element_multiple_choice_max_less_than_min()
    {
        $data = $this->validData();
        $data["params"]["max_elements"] = $data["params"]["min_elements"]--;

        $response = $this->post('/api/evaluation-tool/survey-elements', $data);
        $response->assertStatus(422);
    }
}
