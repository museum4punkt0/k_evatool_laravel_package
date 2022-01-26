<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ExtendEvaluationToolTranscriptions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('evaluation_tool_audio_transcriptions', function (Blueprint $table) {
            $table->string('status', 50)->nullable()->after('api_transcription');
            $table->string('service', 50)->nullable()->after('api_transcription');
            $table->string('transaction_id', 50)->nullable()->after('api_transcription');
            $table->json('result_payload')->nullable()->after('api_transcription');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('evaluation_tool_audio_transcriptions', function (Blueprint $table) {
            $table->dropColumn('status');
            $table->dropColumn('service');
            $table->dropColumn('transaction_id');
            $table->dropColumn('result_payload');
        });
    }
}
