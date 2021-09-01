<?php

namespace Tests\Feature;

use Faker\Factory;
use Tests\TestCase;
use Twoavy\EvaluationTool\Factories\EvaluationToolSurveyFactory;
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
        $survey = EvaluationToolSurvey::all()->random(1)[0];
        $response = $this->get('/api/evaluation-tool/surveys/' . $survey->id);
        $response->assertStatus(200);
    }

    public function test_create_survey()
    {
        $data = [
            "name" => "Test",
        ];
        $response = $this->post('/api/evaluation-tool/surveys', $data);
        $response->assertStatus(200);
    }

    public function test_create_survey_without_name()
    {
        $data = [];
        $response = $this->post('/api/evaluation-tool/surveys', $data);
        $response->assertStatus(422);
    }

    public function test_create_survey_with_name_too_short()
    {
        $data = [
            "name" => "A",
        ];
        $response = $this->post('/api/evaluation-tool/surveys', $data);
        $response->assertStatus(422);
    }

    public function test_create_survey_with_name_too_long()
    {
        $faker = Factory::create();
        $data = [
            "name" => $faker->words(100, true),
        ];
        $response = $this->post('/api/evaluation-tool/surveys', $data);
        $response->assertStatus(422);
    }

    public function test_update_survey()
    {
        $survey = EvaluationToolSurvey::all()->random(1)[0];
        $data = $survey->toArray();
        $response = $this->put('/api/evaluation-tool/surveys/' . $survey->id, $data);
        $response->assertStatus(200);
    }

    public function test_delete_survey_without_steps()
    {
        EvaluationToolSurveyFactory::times(1)->create();
        $survey = EvaluationToolSurvey::all()->last();
        $response = $this->delete('/api/evaluation-tool/surveys/' . $survey->id);
        $response->assertStatus(200);
    }

    public function test_delete_survey_with_steps()
    {
        $surveyStep = EvaluationToolSurveyStep::all()->last();
        $survey = EvaluationToolSurvey::find($surveyStep->survey_id);
        $response = $this->delete('/api/evaluation-tool/surveys/' . $survey->id);
        $response->assertStatus(409);
    }
}
