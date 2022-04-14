<?php

use Hashids\Hashids;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Twoavy\EvaluationTool\Models\EvaluationToolSurveyStep;

class EvaluationToolSurveyStepAddSlug extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('evaluation_tool_survey_steps', function (Blueprint $table) {
            $table->string('slug', 6)->nullable()->after('name')->unique()->key();;
        });

        // create slugs
        EvaluationToolSurveyStep::withTrashed()->get()->each(function ($surveyStep) {
            $hashids          = new Hashids('evatool' . env('APP_URL'), 6, 'abcdefghijklmnopqrstuvwxyz1234567890');
            $surveyStep->slug = $hashids->encode($surveyStep->id);
            $surveyStep->timestamps = false;
            $surveyStep->saveQuietly();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('evaluation_tool_survey_steps', function (Blueprint $table) {
            $table->dropColumn('slug');
        });
    }
}
