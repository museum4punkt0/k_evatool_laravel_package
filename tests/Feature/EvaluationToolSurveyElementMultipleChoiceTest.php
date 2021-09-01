<?php

namespace Tests\Feature;

use Database\Seeders\DatabaseSeeder;
use Tests\TestCase;
use Twoavy\EvaluationTool\Helpers\EvaluationToolHelper;
use Twoavy\EvaluationTool\Models\EvaluationToolSurveyElement;

class EvaluationToolSurveyElementMultipleChoiceTest extends TestCase
{
    private function validData(): array
    {
        return [
            "name"   => "Test",
            "params" => [
                "min_elements" => 2,
                "max_elements" => 2,
                "options"      => [
                    [
                        "de" => "Option 1 DE",
                        "en" => "Option 1 EN"
                    ],
                    [
                        "de" => "Option 2 DE",
                        "en" => "Option 2 EN"
                    ],
                ]
            ],
        ];
    }

    public function test_create_survey_element_multiple_choice_without_name()
    {
        $data = $this->validData();
        unset($data["name"]);

        $response = $this->post('/api/evaluation-tool/survey-elements', $data);
        $response->assertStatus(422);
    }

    public function test_create_survey_element_multiple_choice_max_less_than_min()
    {
        $data                           = $this->validData();
        $data["params"]["max_selected"] = $data["params"]["min_selected"]--;

        $response = $this->post('/api/evaluation-tool/survey-elements', $data);
        $response->assertStatus(422);
    }

    public function test_update_survey_element_multiple_choice_without_name()
    {
        $surveyElement = EvaluationToolSurveyElement::where("survey_element_type_id", 2)->first();

        $transformed = EvaluationToolHelper::transformModel($surveyElement);
        unset($transformed["name"]);

        $response = $this->post('/api/evaluation-tool/survey-elements/' . $surveyElement->id, $transformed);
        $response->assertStatus(422);
    }


}
