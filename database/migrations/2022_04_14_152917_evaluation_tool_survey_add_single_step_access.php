<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class EvaluationToolSurveyAddSingleStepAccess extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('evaluation_tool_surveys', function (Blueprint $table) {
            $table->boolean('single_step_access')->default(false)->after('setting_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('evaluation_tool_surveys', function (Blueprint $table) {
            $table->dropColumn('single_step_access');
        });
    }
}
