<?php

use Illuminate\Support\Facades\Route;
use Twoavy\EvaluationTool\Http\Controllers\EvaluationToolSurveyLanguageController;
use Twoavy\EvaluationTool\Http\Controllers\EvaluationToolSurveyLocalizationController;
use Twoavy\EvaluationTool\Http\Controllers\EvaluationToolSurveyController;
use Twoavy\EvaluationTool\Http\Controllers\EvaluationToolSurveyStepController;
use Twoavy\EvaluationTool\Http\Controllers\EvaluationToolSurveyElementController;
use Twoavy\EvaluationTool\Http\Controllers\EvaluationToolSurveyStepTypeController;
use Twoavy\EvaluationTool\Http\Controllers\EvaluationToolSurveyStepResultController;
use Twoavy\EvaluationTool\Http\Controllers\EvaluationToolSurveyStepResultAssetController;
use Twoavy\EvaluationTool\Http\Controllers\EvaluationToolSurveySurveyStepController;

Route::prefix('api/evaluation-tool')->group(function () {
    Route::apiResource('survey-languages', EvaluationToolSurveyLanguageController::class);
    Route::apiResource('survey-localizations', EvaluationToolSurveyLocalizationController::class);
    Route::apiResource('surveys', EvaluationToolSurveyController::class);
    Route::apiResource('survey-steps', EvaluationToolSurveyStepController::class);
    Route::apiResource('survey-elements', EvaluationToolSurveyElementController::class);
    Route::apiResource('survey-step-types', EvaluationToolSurveyStepTypeController::class);
    Route::apiResource('survey-step-results', EvaluationToolSurveyStepResultController::class);
    Route::apiResource('survey-step-result-assets', EvaluationToolSurveyStepResultAssetController::class);
    Route::apiResource('surveys.survey-steps', EvaluationToolSurveySurveyStepController::class);
});
