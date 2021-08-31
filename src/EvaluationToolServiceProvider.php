<?php

namespace Twoavy\EvaluationTool;

use Illuminate\Support\ServiceProvider;
use Twoavy\EvaluationTool\Console\Commands\TestCommand;
use Twoavy\EvaluationTool\Console\Commands\TypesCommand;
use Twoavy\EvaluationTool\Models\EvaluationToolSurvey;
use Twoavy\EvaluationTool\Models\EvaluationToolSurveyElement;
use Twoavy\EvaluationTool\Observers\EvaluationToolSurveyObserver;
use Twoavy\EvaluationTool\Observers\EvaluationToolSurveyElementObserver;

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

        // observers
        EvaluationToolSurvey::observe(EvaluationToolSurveyObserver::class);
        EvaluationToolSurveyElement::observe(EvaluationToolSurveyElementObserver::class);

        if ($this->app->runningInConsole()) {
            $this->commands([
                TestCommand::class,
                TypesCommand::class
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
                ], 'migrations');
            }
        }
    }

    protected function publishResources()
    {
    }
}
