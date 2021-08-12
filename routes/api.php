<?php

use Illuminate\Support\Facades\Route;
use Twoavy\EvaluationTool\Http\Controllers\EvaluationToolSurveysController;
use Twoavy\EvaluationTool\Http\Controllers\EvaluationToolSurveyStepsController;

Route::prefix('api/evaluation-tool')->group(function () {
    Route::apiResource('surveys', EvaluationToolSurveysController::class);
    Route::apiResource('survey-steps', EvaluationToolSurveyStepsController::class);
    Route::apiResource('surveys.survey-steps', EvaluationToolSurveysSurveyStepsController::class);
});
