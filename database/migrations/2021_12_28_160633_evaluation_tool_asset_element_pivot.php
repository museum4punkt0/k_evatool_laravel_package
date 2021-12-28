<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class EvaluationToolAssetElementPivot extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('evaluation_tool_asset_survey_element', function (Blueprint $table) {
            $table->unsignedBigInteger('evaluation_tool_asset_id');
            $table->unsignedBigInteger('evaluation_tool_survey_element_id');
            $table->primary(['evaluation_tool_asset_id', 'evaluation_tool_survey_element_id'], "asset_survey_element");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('evaluation_tool_asset_survey_element');
    }
}
