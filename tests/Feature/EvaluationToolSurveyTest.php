<?php

namespace Tests\Feature;

use Database\Seeders\DatabaseSeeder;
use Faker\Factory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Twoavy\EvaluationTool\Models\EvaluationToolSurvey;

class EvaluationToolSurveyTest extends TestCase
{

    use RefreshDatabase;

    public function test_get_surveys()
    {
        $this->seed(DatabaseSeeder::class);
        $response = $this->get('/api/evaluation-tool/surveys');
        $response->assertStatus(200);
    }

    public function test_get_survey()
    {
        $this->seed(DatabaseSeeder::class);
        $survey   = EvaluationToolSurvey::all()->random(1)[0];
        $response = $this->get('/api/evaluation-tool/surveys/' . $survey->id);
        $response->assertStatus(200);
    }

    public function test_create_survey()
    {
        $this->seed(DatabaseSeeder::class);
        $data     = [
            "name" => "Test"
        ];
        $response = $this->post('/api/evaluation-tool/surveys', $data);
        $response->assertStatus(200);
    }

    public function test_create_survey_without_name()
    {
        $this->seed(DatabaseSeeder::class);
        $data     = [];
        $response = $this->post('/api/evaluation-tool/surveys', $data);
        $response->assertStatus(422);
    }

    public function test_create_survey_with_name_too_short()
    {
        $this->seed(DatabaseSeeder::class);
        $data     = [
            "name" => "A"
        ];
        $response = $this->post('/api/evaluation-tool/surveys', $data);
        $response->assertStatus(422);
    }

    public function test_create_survey_with_name_too_long()
    {
        $faker = Factory::create();
        $this->seed(DatabaseSeeder::class);
        $data = [
            "name" => $faker->words(100, true)
        ];
        $response = $this->post('/api/evaluation-tool/surveys', $data);
        $response->assertStatus(422);
    }

    public function test_update_survey()
    {
        $this->seed(DatabaseSeeder::class);
        $survey   = EvaluationToolSurvey::all()->random(1)[0];
        $data     = $survey->toArray();
        $response = $this->put('/api/evaluation-tool/surveys/' . $survey->id, $data);
        $response->assertStatus(200);
    }

    public function test_delete_survey()
    {
        $this->seed(DatabaseSeeder::class);
        $survey   = EvaluationToolSurvey::all()->random(1)[0];
        $response = $this->delete('/api/evaluation-tool/surveys/' . $survey->id);
        $response->assertStatus(200);
    }
}
