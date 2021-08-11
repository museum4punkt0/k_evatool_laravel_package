<?php

use Illuminate\Support\Facades\Route;
use Twoavy\EvaluationTool\Http\Controllers\EvaluationToolSurveyController;

Route::prefix('api/evaluation-tool')->group(function () {
    Route::apiResource('surveys', EvaluationToolSurveyController::class);
});
