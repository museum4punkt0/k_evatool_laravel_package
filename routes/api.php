<?php

use Illuminate\Support\Facades\Route;
use Twoavy\EvaluationTool\Http\Controllers\EvaluationToolAssetController;
use Twoavy\EvaluationTool\Http\Controllers\EvaluationToolAudioTranscriptionController;
use Twoavy\EvaluationTool\Http\Controllers\EvaluationToolSurveyElementSurveyController;
use Twoavy\EvaluationTool\Http\Controllers\EvaluationToolSurveyLanguageController;
use Twoavy\EvaluationTool\Http\Controllers\EvaluationToolSurveyController;
use Twoavy\EvaluationTool\Http\Controllers\EvaluationToolSurveyElementController;
use Twoavy\EvaluationTool\Http\Controllers\EvaluationToolSurveyElementTypeController;
use Twoavy\EvaluationTool\Http\Controllers\EvaluationToolSurveyStatsExportController;
use Twoavy\EvaluationTool\Http\Controllers\EvaluationToolSurveyStepResultAssetTranscriptionController;
use Twoavy\EvaluationTool\Http\Controllers\EvaluationToolSurveyStatsController;
use Twoavy\EvaluationTool\Http\Controllers\EvaluationToolSurveySurveyResultController;
use Twoavy\EvaluationTool\Http\Controllers\EvaluationToolSurveySurveyStepSurveyStepResultController;
use Twoavy\EvaluationTool\Http\Controllers\EvaluationToolSurveySurveyRunController;
use Twoavy\EvaluationTool\Http\Controllers\EvaluationToolSurveySurveyStepController;
use Twoavy\EvaluationTool\Http\Controllers\EvaluationToolSurveySeedController;
use Twoavy\EvaluationTool\Http\Controllers\SpeechmaticsController;
use Twoavy\EvaluationTool\Http\Controllers\EvaluationToolSettingController;

Route::prefix('api/evaluation-tool')
    ->middleware(['api'])
    ->group(function () {
        Route::apiResource('survey-languages', EvaluationToolSurveyLanguageController::class);
        Route::apiResource('assets', EvaluationToolAssetController::class);
        Route::post('surveys/{survey}/duplicate', [EvaluationToolSurveyController::class, "duplicateSurvey"]);
        Route::post('surveys/{survey}/publish', [EvaluationToolSurveyController::class, "publishSurvey"]);
        Route::post('surveys/{survey}/archive', [EvaluationToolSurveyController::class, "archiveSurvey"]);
        Route::put('surveys/{survey}/admin-layout', [EvaluationToolSurveyController::class, "updateAdminLayout"]);
        Route::apiResource('survey-elements', EvaluationToolSurveyElementController::class);
        Route::apiResource('survey-elements.surveys', EvaluationToolSurveyElementSurveyController::class)->only("index");
        Route::apiResource('survey-element-types', EvaluationToolSurveyElementTypeController::class);
        Route::post('surveys/{survey}/steps/{step}/set-start-step', [EvaluationToolSurveySurveyStepController::class, "setStartStep"]);
        Route::post('surveys/{survey}/steps/{step}/set-next-step', [EvaluationToolSurveySurveyStepController::class, "setNextStep"]);
        Route::post('surveys/{survey}/steps/{step}/remove-next-step', [EvaluationToolSurveySurveyStepController::class, "removeNextStep"]);
        Route::apiResource('surveys', EvaluationToolSurveyController::class);
        Route::get('surveys/{surveySlug}/run', [EvaluationToolSurveySurveyRunController::class, "index"]);
        Route::get('surveys/{stepSlug}/run-step', [EvaluationToolSurveySurveyRunController::class, "indexStep"]);
        Route::post('surveys/{surveySlug}/run/asset', [EvaluationToolSurveySurveyRunController::class, "storeAsset"]);
        Route::post('surveys/{surveySlug}/run', [EvaluationToolSurveySurveyRunController::class, "store"]);
        Route::post('surveys/{stepSlug}/run-step', [EvaluationToolSurveySurveyRunController::class, "storeStep"]);
        Route::get('surveys/{surveySlug}/path', [EvaluationToolSurveySurveyRunController::class, "getSurveyPath"]);
        Route::apiResource('surveys.steps', EvaluationToolSurveySurveyStepController::class);
        Route::apiResource('surveys.results', EvaluationToolSurveySurveyResultController::class);
        Route::apiResource('surveys.steps.results', EvaluationToolSurveySurveyStepSurveyStepResultController::class);
        Route::get('transcriptions/{resultAsset}', [SpeechmaticsController::class, "getTranscription"]);
        Route::post('transcriptions/{resultAsset}', [EvaluationToolAudioTranscriptionController::class, "setManualTranscription"]);
        Route::apiResource('survey-step-result-assets.transcriptions', EvaluationToolSurveyStepResultAssetTranscriptionController::class)->only("store");
        Route::post('surveys/{survey}/stats-export', [EvaluationToolSurveyStatsExportController::class, "getStatsExport"]);
        Route::post('surveys/{survey}/stats-download', [EvaluationToolSurveyStatsExportController::class, "downloadStatsExport"]);
        Route::get('surveys/{survey}/seed', [EvaluationToolSurveySeedController::class, "seedResults"]);
        Route::get('surveys/{survey}/stats', [EvaluationToolSurveyStatsController::class, "getStats"]);
        Route::get('surveys/{survey}/stats/{step}', [EvaluationToolSurveyStatsController::class, "getStatsByStep"]);
        Route::get('surveys/{survey}/stats-cache', [EvaluationToolSurveyStatsController::class, "getStatsCache"]);
        Route::get('surveys/{survey}/stats-list', [EvaluationToolSurveyStatsController::class, "getStatsList"]);
        Route::get('surveys/{survey}/stats-trend', [EvaluationToolSurveyStatsController::class, "getStatsTrend"]);
        Route::get('surveys/{survey}/stats-list-scheme', [EvaluationToolSurveyStatsController::class, "getStatsListScheme"]);
        Route::apiResource('settings', EvaluationToolSettingController::class);
        Route::post('settings/{setting}/settings-asset', [EvaluationToolSettingController::class, "storeAsset"]);
    });

Route::any('/tus/{any?}', function () {
    return app('tus-server')->serve();
})->where('any', '.*');

