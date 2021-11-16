<?php

namespace Twoavy\EvaluationTool;

use Illuminate\Auth\Events\Authenticated;
use TusPhp\Events\TusEvent;
use TusPhp\Tus\Server as TusServer;
use Illuminate\Support\ServiceProvider;
use Twoavy\EvaluationTool\Console\Commands\TestCommand;
use Twoavy\EvaluationTool\Console\Commands\TypesCommand;
use Twoavy\EvaluationTool\Console\Commands\SeedSurveyResultsCommand;
use Twoavy\EvaluationTool\Models\EvaluationToolAsset;
use Twoavy\EvaluationTool\Models\EvaluationToolSurvey;
use Twoavy\EvaluationTool\Models\EvaluationToolSurveyElement;
use Twoavy\EvaluationTool\Models\EvaluationToolSurveyLanguage;
use Twoavy\EvaluationTool\Models\EvaluationToolSurveyStep;
use Twoavy\EvaluationTool\Models\EvaluationToolSurveyStepResult;
use Twoavy\EvaluationTool\Observers\EvaluationToolAssetObserver;
use Twoavy\EvaluationTool\Observers\EvaluationToolSurveyLanguageObserver;
use Twoavy\EvaluationTool\Observers\EvaluationToolSurveyObserver;
use Twoavy\EvaluationTool\Observers\EvaluationToolSurveyElementObserver;
use Twoavy\EvaluationTool\Observers\EvaluationToolSurveyStepObserver;
use Twoavy\EvaluationTool\Observers\EvaluationToolSurveyStepResultObserver;

class EvaluationToolServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->config["filesystems.disks.evaluation_tool_assets"] = [
            'driver' => 'local',
            'root'   => storage_path('app/evaluation-tool/assets'),
            'url'    => env('APP_URL') . "/evaluation-tool",
        ];

        $this->app->config["filesystems.disks.evaluation_tool_uploads"] = [
            'driver' => 'local',
            'root'   => storage_path('app/evaluation-tool/uploads'),
        ];

        $this->app->config["filesystems.disks.evaluation_tool_demo_assets"] = [
            'driver' => 'local',
            'root'   => base_path('packages/twoavy/evaluation-tool/assets'),
        ];

        $this->app->config["filesystems.disks.evaluation_tool_audio"] = [
            'driver' => 'local',
            'root'   => storage_path('app/evaluation-tool/audio'),
        ];

        // add tus server
        $this->app->singleton('tus-server', function () {
            $server = new TusServer('file');

            $server
                ->setApiPath('/tus')
                ->setUploadDir(storage_path('app/evaluation-tool/uploads'));

            $server->event()->addListener('tus-server.upload.created', function (TusEvent $event) {
            });

            $server->event()->addListener('tus-server.upload.progress', function (TusEvent $event) {
            });

            $server->event()->addListener('tus-server.upload.complete', function (TusEvent $event) {
                (new Http\Controllers\EvaluationToolAssetController)->createTusAsset($event->getFile()->details());
            });

            $server->event()->addListener('tus-server.upload.merged', function (TusEvent $event) {
            });

            return $server;
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadRoutesFrom(__DIR__ . '/../routes/api.php');

        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        // observers
        EvaluationToolAsset::observe(EvaluationToolAssetObserver::class);
        EvaluationToolSurvey::observe(EvaluationToolSurveyObserver::class);
        EvaluationToolSurveyStep::observe(EvaluationToolSurveyStepObserver::class);
        EvaluationToolSurveyElement::observe(EvaluationToolSurveyElementObserver::class);
        EvaluationToolSurveyLanguage::observe(EvaluationToolSurveyLanguageObserver::class);
        EvaluationToolSurveyStepResult::observe(EvaluationToolSurveyStepResultObserver::class);

        if ($this->app->runningInConsole()) {
            $this->commands([
                TestCommand::class,
                TypesCommand::class,
                SeedSurveyResultsCommand::class
            ]);

            if (!class_exists('CreateEvaluationToolSurveysTable')) {
                $this->publishes([
                    __DIR__ . '/../database/migrations/create_evaluation_tool_surveys_table.php'                   => database_path('migrations/' . date('Y_m_d_His', time()) . '_create_evaluation_tool_surveys_table.php'),
                    __DIR__ . '/../database/migrations/create_evaluation_tool_survey_steps_table.php'              => database_path('migrations/' . date('Y_m_d_His', time()) . '_create_evaluation_tool_survey_steps_table.php'),
                    __DIR__ . '/../database/migrations/create_evaluation_tool_survey_element_types_table.php'      => database_path('migrations/' . date('Y_m_d_His', time()) . '_create_evaluation_tool_survey_element_types_table.php'),
                    __DIR__ . '/../database/migrations/create_evaluation_tool_survey_step_results_table.php'       => database_path('migrations/' . date('Y_m_d_His', time()) . '_create_evaluation_tool_survey_step_results_table.php'),
                    __DIR__ . '/../database/migrations/create_evaluation_tool_survey_step_result_assets_table.php' => database_path('migrations/' . date('Y_m_d_His', time()) . '_create_evaluation_tool_survey_step_result_assets_table.php'),
                    __DIR__ . '/../database/migrations/create_evaluation_tool_survey_elements_table.php'           => database_path('migrations/' . date('Y_m_d_His', time()) . '_create_evaluation_tool_survey_elements_table.php'),
                    __DIR__ . '/../database/migrations/create_evaluation_tool_survey_languages_table.php'          => database_path('migrations/' . date('Y_m_d_His', time()) . '_create_evaluation_tool_survey_languages_table.php'),
                    __DIR__ . '/../database/migrations/create_evaluation_tool_survey_localizations_table.php'      => database_path('migrations/' . date('Y_m_d_His', time()) . '_create_evaluation_tool_survey_localizations_table.php'),
                    __DIR__ . '/../database/migrations/create_evaluation_tool_assets_table.php'                    => database_path('migrations/' . date('Y_m_d_His', time()) . '_create_evaluation_tool_assets_table.php'),
                ], 'migrations');
            }
        }
    }

    protected function publishResources()
    {
    }
}
