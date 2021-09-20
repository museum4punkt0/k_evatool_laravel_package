<?php

use Illuminate\Support\Facades\Route;
use Twoavy\EvaluationTool\Http\Controllers\EvaluationToolAssetController;
use Twoavy\EvaluationTool\Http\Controllers\EvaluationToolSurveyLanguageController;
use Twoavy\EvaluationTool\Http\Controllers\EvaluationToolSurveyLocalizationController;
use Twoavy\EvaluationTool\Http\Controllers\EvaluationToolSurveyController;
use Twoavy\EvaluationTool\Http\Controllers\EvaluationToolSurveyStepController;
use Twoavy\EvaluationTool\Http\Controllers\EvaluationToolSurveyElementController;
use Twoavy\EvaluationTool\Http\Controllers\EvaluationToolSurveyElementTypeController;
use Twoavy\EvaluationTool\Http\Controllers\EvaluationToolSurveyStepResultController;
use Twoavy\EvaluationTool\Http\Controllers\EvaluationToolSurveyStepResultAssetController;
use Twoavy\EvaluationTool\Http\Controllers\EvaluationToolSurveySurveyStepController;

Route::prefix('api/evaluation-tool')
    ->middleware(['api'])
    ->group(function () {
        Route::apiResource('survey-languages', EvaluationToolSurveyLanguageController::class);
        Route::apiResource('survey-localizations', EvaluationToolSurveyLocalizationController::class);
        Route::apiResource('assets', EvaluationToolAssetController::class);
        Route::apiResource('surveys', EvaluationToolSurveyController::class);
        Route::put('surveys/{survey}/admin-layout', [EvaluationToolSurveyController::class, "updateAdminLayout"]);
        Route::apiResource('survey-elements', EvaluationToolSurveyElementController::class);
        Route::apiResource('survey-element-types', EvaluationToolSurveyElementTypeController::class);
        Route::apiResource('survey-step-results', EvaluationToolSurveyStepResultController::class);
        Route::apiResource('survey-step-result-assets', EvaluationToolSurveyStepResultAssetController::class);
        Route::apiResource('surveys.survey-steps', EvaluationToolSurveySurveyStepController::class);
    });

Route::any('/tus/{any?}', function () {
    return app('tus-server')->serve();
})->middleware('auth:api')->where('any', '.*');

