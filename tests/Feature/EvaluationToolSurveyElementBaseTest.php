<?php

namespace Tests\Feature;

use Tests\TestCase;
use Twoavy\EvaluationTool\Helpers\EvaluationToolHelper;
use Twoavy\EvaluationTool\Models\EvaluationToolSurveyElement;
use Twoavy\EvaluationTool\Helpers\TestHelper;

class EvaluationToolSurveyElementTypeTest extends TestCase
{
    // TODO: simplest type, e.g. info type
    private function validData(): array
    {
        return [
            "name" => "Test",
            "survey_element_type" => "multipleChoice",
            "params" => [
                "minSelectable" => 2,
                "maxSelectable" => 2,
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

    public function test_create_survey_element_type_without_name()
    {
        $headers = TestHelper::getAuthHeader();
        $data = $this->validData();
        unset($data["name"]);

        $response = $this->post('/api/evaluation-tool/survey-elements', $data, $headers);
        $response->assertStatus(422);
    }

    public function test_update_survey_element_without_name()
    {
        $headers = TestHelper::getAuthHeader();
        $surveyElement = EvaluationToolSurveyElement::where("survey_element_type_id", 2)->first();

        $transformed = EvaluationToolHelper::transformModel($surveyElement);
        unset($transformed["name"]);

        // dd($transformed);
        // dd($headers);
        $response = $this->put('/api/evaluation-tool/survey-elements/' . $surveyElement->id, array_merge($transformed, $headers));
        $response->assertStatus(422);
    }

    public function test_create_survey_element_with_too_short_name()
    {
        $headers = TestHelper::getAuthHeader();
        $data = $this->validData();
        $data["name"] = "";

        $response = $this->post('/api/evaluation-tool/survey-elements', $data, $headers);
        $response->assertStatus(422);
    }

    public function test_update_survey_element_with_too_short_name()
    {
        $headers = TestHelper::getAuthHeader();
        $surveyElement = EvaluationToolSurveyElement::where("survey_element_type_id", 2)->first();

        $transformed = EvaluationToolHelper::transformModel($surveyElement);
        $transformed["name"] = "";

        $response = $this->put('/api/evaluation-tool/survey-elements/' . $surveyElement->id, array_merge($transformed, $headers));
        $response->assertStatus(422);
    }

    public function test_create_survey_element_without_params()
    {
        $headers = TestHelper::getAuthHeader();
        $data = $this->validData();
        unset($data["params"]);

        $response = $this->post('/api/evaluation-tool/survey-elements', $data, $headers);
        $response->assertStatus(422);
    }

    public function test_update_survey_element_without_params()
    {
        $headers = TestHelper::getAuthHeader();
        $surveyElement = EvaluationToolSurveyElement::where("survey_element_type_id", 2)->first();

        $transformed = EvaluationToolHelper::transformModel($surveyElement);
        unset($transformed["params"]);

        $response = $this->put('/api/evaluation-tool/survey-elements/' . $surveyElement->id, array_merge($transformed, $headers));
        $response->assertStatus(422);
    }
}
