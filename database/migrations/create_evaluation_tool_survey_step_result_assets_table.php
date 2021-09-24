<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEvaluationToolSurveyStepResultAssetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('evaluation_tool_survey_step_result_assets', function (Blueprint $table) {
            $table->id();
            $table->string('filename', 50);
            $table->string('hash', 100);
            $table->string('mime', 50);
            $table->json('meta')->nullable();
            $table->integer('size');
            $table->text('transcription')->nullable();
            $table->unsignedBigInteger('survey_step_result_id');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table('evaluation_tool_survey_step_result_assets', function (Blueprint $table) {
            $table->unsignedBigInteger('created_by')->nullable()->after('created_at');
            $table->unsignedBigInteger('updated_by')->nullable()->after('updated_at');
            $table->unsignedBigInteger('deleted_by')->nullable()->after('deleted_at');
        });

        Schema::table('evaluation_tool_survey_step_result_assets', function (Blueprint $table) {
            $table->unique('hash');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('evaluation_tool_survey_step_result_assets');
    }
}
