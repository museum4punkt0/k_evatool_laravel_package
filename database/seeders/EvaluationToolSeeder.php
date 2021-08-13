<?php

namespace Twoavy\EvaluationTool\Seeders;

use Database\Seeders\EvaluationToolSurveySeeder;
use Illuminate\Database\Seeder;

class EvaluationToolSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            EvaluationToolSurveySeeder::class
        ]);
    }
}
