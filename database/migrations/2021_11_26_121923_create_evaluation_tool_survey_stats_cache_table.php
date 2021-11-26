<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEvaluationToolSurveyStatsCacheTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('evaluation_tool_survey_stats_caches', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("survey_id");
            $table->date("date");
            $table->text("results")->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('evaluation_tool_survey_stats_caches');
    }
}
