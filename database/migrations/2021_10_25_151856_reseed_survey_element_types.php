<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Twoavy\EvaluationTool\Http\Controllers\EvaluationToolSurveyElementTypeController;

class ReseedSurveyElementTypes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        EvaluationToolSurveyElementTypeController::seedSurveyElementTypes();
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
