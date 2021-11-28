<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class EvaluationToolSurveyStepResultsAddSurveyFinished extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('evaluation_tool_survey_step_results', function (Blueprint $table) {
            $table->boolean("survey_finished")->default(false)->after("demo");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('evaluation_tool_survey_step_results', function (Blueprint $table) {
            $table->dropColumn("cached");
        });
    }
}
