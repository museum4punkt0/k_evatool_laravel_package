<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEvaluationToolAudioTranscriptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('evaluation_tool_audio_transcriptions', function (Blueprint $table) {
            $table->id();
            $table->text('api_transcription')->nullable();
            $table->text('manual_transcription')->nullable();
            $table->timestamps();
        });

        Schema::table('evaluation_tool_audio_transcriptions', function (Blueprint $table) {
            $table->unsignedBigInteger('created_by')->nullable()->after('created_at');
            $table->unsignedBigInteger('updated_by')->nullable()->after('updated_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('evaluation_tool_audio_transcriptions');
    }
}
