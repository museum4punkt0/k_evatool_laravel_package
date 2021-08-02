<?php

use Illuminate\Support\Facades\Route;
use Twoavy\EvaluationTool\EvaluationToolSurveyController;

//Route::get('api/surveys', [EvaluationToolSurveyController::class, "index"]);

Route::prefix('api/evaluation-tool')->group(function () {
    Route::apiResource('surveys', EvaluationToolSurveyController::class);
});
