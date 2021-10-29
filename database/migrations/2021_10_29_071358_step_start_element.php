<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class StepStartElement extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('evaluation_tool_survey_steps', function (Blueprint $table) {
            $table->boolean('is_first_step')->nullable()->after('time_based_steps');
            $table->unique(['is_first_step', 'survey_id'], "first_step");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
