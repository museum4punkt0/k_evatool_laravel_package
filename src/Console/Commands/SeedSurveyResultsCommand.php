<?php

namespace Twoavy\EvaluationTool\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Twoavy\EvaluationTool\Models\EvaluationToolSurvey;
use Twoavy\EvaluationTool\Http\Controllers\EvaluationToolSurveySeedController;

class SeedSurveyResultsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'evaluation:seed_survey_results {survey_id} {count} {{--truncate}}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Evaluation tool seed survey results command';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $surveyId = $this->argument('survey_id');
        $count    = $this->argument("count");

        if ($this->option('truncate')) {
            DB::table("evaluation_tool_survey_step_results")->truncate();
        }

        if (!$survey = EvaluationToolSurvey::find($surveyId)) {
            $this->info("survey not found");
            return -1;
        }
        $controller = new EvaluationToolSurveySeedController();
        $controller->seedResults($survey, $count);

        $this->info("Evaluation tool seed survey results successful");
        return 0;
    }
}
