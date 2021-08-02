<?php

namespace Twoavy\EvaluationTool;

use Illuminate\Support\ServiceProvider;

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
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        // publish seeder
        $this->publishes([
            __DIR__ . '/../database/seeders/EvaluationToolSeeder.php' => database_path('seeders/EvaluationToolSeeder.php'),
        ]);

        // publish factories
        $this->publishes([
            __DIR__ . '/../database/factories/EvaluationToolSurveyFactory.php' => database_path('factories/EvaluationToolSurveyFactory.php'),
        ]);
    }

    protected function publishResources()
    {
    }
}
