<?php

namespace Twoavy\EvaluationTool\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Spatie\DbSnapshots\Snapshot;
use Twoavy\EvaluationTool\Http\Controllers\EvaluationToolMaintenanceController;
use Twoavy\EvaluationTool\Http\Controllers\EvaluationToolSurveyElementTypeController;
use Twoavy\EvaluationTool\Models\EvaluationToolSurvey;

class ClearResultsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'evaluation:clear-results';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Evaluation tool clear results';

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
        $surveyId = $this->ask('What is your survey id?');

        if (!$survey = EvaluationToolSurvey::find($surveyId)) {
            $this->error("survey not found");
            return 0;
        }

        $resultType = $this->choice(
            'Which result shall be cleared?',
            ['live', 'demo'],
            1
        );

        $createDbBackup = $this->choice(
            'Create database backup?',
            ['yes', 'no'],
            0
        );

        $this->info($createDbBackup);

        if ($resultType == "live") {
            $count = $survey->survey_results()->count();
            $this->info($count . " live result(s)");
            if ($count > 0) {
                if ($createDbBackup === "yes") {
                    $this->call('snapshot:create');
                }
                EvaluationToolMaintenanceController::clearLiveResults($survey);
                return 0;
            }
        }

        if ($resultType == "demo") {
            $count = $survey->survey_demo_results()->count();
            $this->info($count . " demo result(s)");
            if ($count > 0) {
                if ($createDbBackup === "yes") {
                    $this->call('snapshot:create');
                }
                EvaluationToolMaintenanceController::clearDemoResults($survey);
                return 0;
            }
        }

        return 0;
    }
}
