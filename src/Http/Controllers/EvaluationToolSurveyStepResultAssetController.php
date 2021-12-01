<?php

namespace Twoavy\EvaluationTool\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Twoavy\EvaluationTool\Http\Requests\EvaluationToolTranscriptionStoreRequest;
use Twoavy\EvaluationTool\Models\EvaluationToolAsset;
use Twoavy\EvaluationTool\Models\EvaluationToolSurveyLanguage;
use Twoavy\EvaluationTool\Models\EvaluationToolSurveyStepResultAsset;
use Twoavy\EvaluationTool\Models\EvaluationToolAudioTranscription;
use Twoavy\EvaluationTool\Traits\EvaluationToolResponse;

class EvaluationToolSurveyStepResultAssetController extends Controller
{
    use EvaluationToolResponse;

    public function __construct()
    {
        $this->audioDisk = Storage::disk("evaluation_tool_audio");
    }

    public function createStepResultAsset($audioData, $surveyStepResultId, $modifier = ""): EvaluationToolSurveyStepResultAsset
    {
        $fileContent = $audioData;
        $fileContent = str_replace('data:audio/wav;base64,', '', $fileContent) . $modifier;
        $hash        = substr(md5($fileContent), 0, 6);
        $filename    = "recording_" . date('ymd_His') . "_" . $hash . ".wav";

        // store file
        $this->audioDisk->put($filename, base64_decode($fileContent));

        $resultAsset                        = new EvaluationToolSurveyStepResultAsset();
        $resultAsset->filename              = $filename;
        $resultAsset->hash                  = hash_file('md5', $this->audioDisk->path($filename));
        $resultAsset->mime                  = mime_content_type($this->audioDisk->path($filename));
        $resultAsset->size                  = $this->audioDisk->size($filename);
        $resultAsset->meta                  = EvaluationToolAssetController::getFileMetaData($this->audioDisk->path($filename));
        $resultAsset->survey_step_result_id = $surveyStepResultId;
        $resultAsset->save();

        return $resultAsset;
    }
}
