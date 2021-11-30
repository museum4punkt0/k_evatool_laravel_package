<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddResultAssetTranscription extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('evaluation_tool_survey_step_result_assets', function (Blueprint $table) {
            $table->dropColumn("transcription");
            $table->unsignedBigInteger("transcription_id")->nullable()->after("survey_step_result_id");
        });

        Schema::table('evaluation_tool_audio_transcriptions', function (Blueprint $table) {
            $table->softDeletes();
            $table->unsignedBigInteger('deleted_by')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('evaluation_tool_survey_step_result_assets', function (Blueprint $table) {
            $table->dropColumn("transcription_id");
            $table->text('transcription')->nullable();
        });

        Schema::table('evaluation_tool_audio_transcriptions', function (Blueprint $table) {
            $table->dropColumn("deleted_at");
            $table->dropColumn("deleted_by");
        });
    }
}
