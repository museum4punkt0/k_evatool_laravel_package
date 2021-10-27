<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class SurveysSurveyLanguagesPivot extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('evaluation_tool_surveys_survey_languages', function (Blueprint $table) {
            $table->unsignedBigInteger('survey_language_id');
            $table->unsignedBigInteger('survey_id');
            $table->primary(['survey_language_id', 'survey_id'], "survey_language");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('evaluation_tool_surveys_survey_languages');
    }
}
