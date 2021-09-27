<?php

use Illuminate\Support\Facades\Route;
use Twoavy\EvaluationTool\Http\Controllers\EvaluationToolAssetController;
use Twoavy\EvaluationTool\Http\Controllers\EvaluationToolSurveyLanguageController;
use Twoavy\EvaluationTool\Http\Controllers\EvaluationToolSurveyController;
use Twoavy\EvaluationTool\Http\Controllers\EvaluationToolSurveyElementController;
use Twoavy\EvaluationTool\Http\Controllers\EvaluationToolSurveyElementTypeController;
use Twoavy\EvaluationTool\Http\Controllers\EvaluationToolSurveySurveyStepSurveyStepResultController;
use Twoavy\EvaluationTool\Http\Controllers\EvaluationToolSurveySurveyStepSurveyStepResultSurveyStepResultAssetController;
use Twoavy\EvaluationTool\Http\Controllers\EvaluationToolSurveySurveyRunController;
use Twoavy\EvaluationTool\Http\Controllers\EvaluationToolSurveySurveyStepController;

Route::prefix('api/evaluation-tool')
    ->middleware(['api'])
    ->group(function () {
        Route::apiResource('survey-languages', EvaluationToolSurveyLanguageController::class);
        Route::apiResource('assets', EvaluationToolAssetController::class);
        Route::post('surveys/{survey}/publish', [EvaluationToolSurveyController::class, "publishSurvey"]);
        Route::put('surveys/{survey}/admin-layout', [EvaluationToolSurveyController::class, "updateAdminLayout"]);
        Route::apiResource('survey-elements', EvaluationToolSurveyElementController::class);
        Route::apiResource('survey-element-types', EvaluationToolSurveyElementTypeController::class);
        Route::post('surveys/{survey}/survey-steps/{surveyStep}/set-next-step', [EvaluationToolSurveySurveyStepController::class, "setNextStep"]);
        Route::post('surveys/{survey}/survey-steps/{surveyStep}/remove-next-step', [EvaluationToolSurveySurveyStepController::class, "removeNextStep"]);
        Route::apiResource('surveys', EvaluationToolSurveyController::class);
        Route::get('surveys/{survey}/run', [EvaluationToolSurveySurveyRunController::class, "index"]);
        Route::post('surveys/{survey}/run', [EvaluationToolSurveySurveyRunController::class, "store"]);
        Route::apiResource('surveys.survey-steps', EvaluationToolSurveySurveyStepController::class);
        Route::apiResource('surveys.survey-steps.survey-step-results', EvaluationToolSurveySurveyStepSurveyStepResultController::class);
        Route::apiResource('surveys.survey-steps.survey-step-results.survey-step-result-assets',
            EvaluationToolSurveySurveyStepSurveyStepResultSurveyStepResultAssetController::class);
    });

Route::any('/tus/{any?}', function () {
    return app('tus-server')->serve();
})->where('any', '.*');

