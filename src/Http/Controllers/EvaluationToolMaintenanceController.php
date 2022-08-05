<?php

namespace Twoavy\EvaluationTool\Http\Controllers;

use App\Http\Controllers\Controller;
use DonatelloZa\RakePlus\RakePlus;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use stdClass;
use Twoavy\EvaluationTool\Helpers\EvaluationToolHelper;
use Twoavy\EvaluationTool\Http\Requests\EvaluationToolSurveyStatsIndexRequest;
use Twoavy\EvaluationTool\Models\EvaluationToolSurvey;
use Twoavy\EvaluationTool\Models\EvaluationToolSurveyLanguage;
use Twoavy\EvaluationTool\Models\EvaluationToolSurveyStatsCache;
use Twoavy\EvaluationTool\Models\EvaluationToolSurveyStep;
use Twoavy\EvaluationTool\Models\EvaluationToolSurveyStepResult;
use Twoavy\EvaluationTool\Traits\EvaluationToolResponse;

class EvaluationToolMaintenanceController extends Controller
{
    use EvaluationToolResponse;

    public static function clearLiveResults(EvaluationToolSurvey $survey) {
        echo "clear live";
        $survey->survey_results()->delete();
    }

    public static function clearDemoResults(EvaluationToolSurvey $survey) {
        $survey->survey_demo_results()->delete();
    }
}
