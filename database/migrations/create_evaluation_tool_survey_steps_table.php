<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEvaluationToolSurveyStepsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('evaluation_tool_survey_steps', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50)->nullable();
            $table->unsignedBigInteger('survey_id');
            $table->unsignedBigInteger('survey_element_id');
            $table->unsignedInteger('group')->nullable();
            $table->unsignedBigInteger('next_step_id')->nullable();
            $table->json('result_based_next_steps')->nullable();
            $table->json('time_based_steps')->nullable();
            $table->boolean('allow_skip')->default(false);
            $table->unsignedBigInteger('parent_step_id')->nullable();
            $table->boolean('published')->default(true);
            $table->timestamp('publish_up')->nullable();
            $table->timestamp('publish_down')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table('evaluation_tool_survey_steps', function (Blueprint $table) {
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
        Schema::dropIfExists('evaluation_tool_survey_steps');
    }
}
