<?php

namespace Twoavy\EvaluationTool\Console\Commands;

use Illuminate\Console\Command;
use Twoavy\EvaluationTool\Http\Controllers\EvaluationToolSurveyElementTypeController;

class TypesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'evaluation:types';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Evaluation tool seed types command';

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
        EvaluationToolSurveyElementTypeController::seedSurveyElementTypes();
        return 0;
    }
}
