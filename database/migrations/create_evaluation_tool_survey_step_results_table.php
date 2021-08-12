<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEvaluationToolSurveyStepResultsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('evaluation_tool_survey_step_results', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('survey_step_id');
            $table->timestamp('presented_at')->nullable();
            $table->timestamp('answered_at')->nullable();
            $table->integer('changed_answer')->default(0);
            $table->uuid('session_id');
            $table->json('result_value');
            $table->boolean('is_skipped');
            // TODO: relative date?
            $table->date('time');
            $table->json('params');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table('evaluation_tool_survey_step_results', function (Blueprint $table) {
            $table->unsignedBigInteger('created_by')->nullable()->after('created_at');
            $table->unsignedBigInteger('updated_by')->nullable()->after('updated_at');
            $table->unsignedBigInteger('deleted_by')->nullable()->after('deleted_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('evaluation_tool_survey_step_results');
    }
}
