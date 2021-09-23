<?php

namespace Tests\Feature;

use Faker\Factory;
use Tests\TestCase;
use Twoavy\EvaluationTool\Factories\EvaluationToolSurveyFactory;
use Twoavy\EvaluationTool\Helpers\TestHelper;
use Twoavy\EvaluationTool\Models\EvaluationToolSurvey;
use Twoavy\EvaluationTool\Models\EvaluationToolSurveyStep;

class EvaluationToolSurveyTest extends TestCase
{
    public function test_get_surveys()
    {
        $response = $this->get('/api/evaluation-tool/surveys');
        $response->assertStatus(200);
    }

    public function test_get_survey()
    {
        $survey   = EvaluationToolSurvey::all()->random(1)[0];
        $response = $this->get('/api/evaluation-tool/surveys/' . $survey->id);

        $response->assertStatus(200);

    }

    public function test_create_survey()
    {
        $headers  = TestHelper::getAuthHeader();

        $data     = [
            "name" => "Test",
        ];
        $response = $this->post('/api/evaluation-tool/surveys', $data, $headers);

        $response->assertStatus(200);

    }

    public function test_create_survey_without_name()
    {
        $headers  = TestHelper::getAuthHeader();
        $data     = [];
        $response = $this->post('/api/evaluation-tool/surveys', $data, $headers);

        $response->assertStatus(422);
    }

    public function test_create_survey_with_name_too_short()
    {
        $headers  = TestHelper::getAuthHeader();
        $data     = [
            "name" => "A",
        ];
        $response = $this->post('/api/evaluation-tool/surveys', $data, $headers);

        $response->assertStatus(422);

    }

    public function test_create_survey_with_name_too_long()
    {
        $headers  = TestHelper::getAuthHeader();
        $faker    = Factory::create();
        $data     = [
            "name" => $faker->words(100, true),
        ];
        $response = $this->post('/api/evaluation-tool/surveys', $data, $headers);

        $response->assertStatus(422);

    }

    public function test_update_survey()
    {
        $headers  = TestHelper::getAuthHeader();
        $survey   = EvaluationToolSurvey::all()->random(1)[0];
        $data     = $survey->toArray();
        $response = $this->put('/api/evaluation-tool/surveys/' . $survey->id, $data, $headers);

        $response->assertStatus(200);

    }

    public function test_delete_survey_without_steps()
    {
        $headers  = TestHelper::getAuthHeader();
        EvaluationToolSurveyFactory::times(1)->create();
        $survey   = EvaluationToolSurvey::all()->last();
        $response = $this->delete('/api/evaluation-tool/surveys/' . $survey->id, $headers);

        $response->assertStatus(200);

    }

    public function test_delete_survey_with_steps()
    {
        $headers  = TestHelper::getAuthHeader();
        $surveyStep = EvaluationToolSurveyStep::all()->last();
        $survey     = EvaluationToolSurvey::find($surveyStep->survey_id);
        $response   = $this->delete('/api/evaluation-tool/surveys/' . $survey->id, $headers);

        $response->assertStatus(409);

    }
}
