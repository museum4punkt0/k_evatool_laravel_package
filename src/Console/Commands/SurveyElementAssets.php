<?php

namespace Twoavy\EvaluationTool\Console\Commands;

use Illuminate\Console\Command;
use Twoavy\EvaluationTool\Http\Controllers\EvaluationToolSurveyElementController;

class SurveyElementAssets extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'evaluation:survey_elements_assets';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fill the survey element asset pivot table';

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
        EvaluationToolSurveyElementController::readSurveyElementAssets();
        return 0;
    }
}
