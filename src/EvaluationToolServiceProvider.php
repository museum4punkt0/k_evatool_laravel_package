<?php

namespace Twoavy\EvaluationTool;

use Illuminate\Support\ServiceProvider;
use Twoavy\EvaluationTool\Console\Commands\TestCommand;

class EvaluationToolServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadRoutesFrom(__DIR__ . '/../routes/api.php');
//        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        if ($this->app->runningInConsole()) {
            $this->commands([
                TestCommand::class,
            ]);

            if (!class_exists('CreateEvaluationToolSurveysTable')) {
                $this->publishes([
                    __DIR__ . '/../database/migrations/create_evaluation_tool_surveys_table.php' => database_path('migrations/' . date('Y_m_d_His', time()) . '_create_evaluation_tool_surveys_table.php'),
                    __DIR__ . '/../database/migrations/create_evaluation_tool_survey_steps_table.php' => database_path('migrations/' . date('Y_m_d_His', time()) . '_create_evaluation_tool_survey_steps_table.php'),
                    __DIR__ . '/../database/migrations/create_evaluation_tool_survey_element_types_table.php' => database_path('migrations/' . date('Y_m_d_His', time()) . '_create_evaluation_tool_survey_element_types_table.php'),
                    __DIR__ . '/../database/migrations/create_evaluation_tool_survey_step_results_table.php' => database_path('migrations/' . date('Y_m_d_His', time()) . '_create_evaluation_tool_survey_step_results_table.php'),
                    __DIR__ . '/../database/migrations/create_evaluation_tool_survey_step_result_assets_table.php' => database_path('migrations/' . date('Y_m_d_His', time()) . '_create_evaluation_tool_survey_step_result_assets_table.php'),
                    __DIR__ . '/../database/migrations/create_evaluation_tool_survey_elements_table.php' => database_path('migrations/' . date('Y_m_d_His', time()) . '_create_evaluation_tool_survey_elements_table.php'),
                    __DIR__ . '/../database/migrations/create_evaluation_tool_survey_languages_table.php' => database_path('migrations/' . date('Y_m_d_His', time()) . '_create_evaluation_tool_survey_languages_table.php'),
                    __DIR__ . '/../database/migrations/create_evaluation_tool_survey_localizations_table.php' => database_path('migrations/' . date('Y_m_d_His', time()) . '_create_evaluation_tool_survey_localizations_table.php'),
                    // you can add any number of migrations here
                ], 'migrations');
            }
        }

        // publish seeder
        $this->publishes([
            __DIR__ . '/../database/seeders/EvaluationToolSeeder.php' => database_path('seeders/EvaluationToolSeeder.php'),
            __DIR__ . '/../database/seeders/EvaluationToolSurveyLanguageSeeder.php' => database_path('seeders/EvaluationToolSurveyLanguageSeeder.php'),
            __DIR__ . '/../database/seeders/EvaluationToolSurveyElementTypeSeeder.php' => database_path('seeders/EvaluationToolSurveyElementTypeSeeder.php'),
        ], 'seeders');

        // publish demo seeder
        $this->publishes([
            __DIR__ . '/../database/seeders/EvaluationToolDemoDataSeeder.php' => database_path('seeders/EvaluationToolDemoDataSeeder.php'),
            __DIR__ . '/../database/seeders/EvaluationToolSurveySeeder.php' => database_path('seeders/EvaluationToolSurveySeeder.php'),
        ], 'demo-seeders');

        // publish factories
        $this->publishes([
            __DIR__ . '/../database/factories/EvaluationToolSurveyLanguageFactory.php' => database_path('factories/EvaluationToolSurveyLanguageFactory.php'),
        ], 'factories');
    }

    protected function publishResources()
    {
    }
}
