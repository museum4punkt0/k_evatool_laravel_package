<?php

namespace Tests\Feature;

use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EvaluationToolSurveyTest extends TestCase
{

//    use RefreshDatabase;

    public function test_get_surveys()
    {
        $this->seed(DatabaseSeeder::class);
        $response = $this->get('/api/evaluation-tool/surveys');
        $response->assertStatus(200);
    }
}
