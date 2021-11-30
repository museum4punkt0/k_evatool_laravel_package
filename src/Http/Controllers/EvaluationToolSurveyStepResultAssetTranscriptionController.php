<?php

namespace Twoavy\EvaluationTool\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Twoavy\EvaluationTool\Http\Requests\EvaluationToolTranscriptionStoreRequest;
use Twoavy\EvaluationTool\Models\EvaluationToolSurveyStepResultAsset;
use Twoavy\EvaluationTool\Models\EvaluationToolAudioTranscription;
use Twoavy\EvaluationTool\Traits\EvaluationToolResponse;

class EvaluationToolSurveyStepResultAssetTranscriptionController extends Controller
{
    use EvaluationToolResponse;

    public function __construct()
    {
        $this->middleware("auth:api");
        $this->audioDisk = Storage::disk("evaluation_tool_audio");
    }

    public function store(EvaluationToolSurveyStepResultAsset $surveyStepResultAsset, EvaluationToolTranscriptionStoreRequest $request)
    {
        if ($surveyStepResultAsset->transcription_id) {
            if (!$transcription = EvaluationToolAudioTranscription::find($surveyStepResultAsset->transcription_id))
                $transcription = new EvaluationToolAudioTranscription();
        } else {
            $transcription = new EvaluationToolAudioTranscription();
        }

        $transcription->manual_transcription = $request->transcription;
        $transcription->save();

        $surveyStepResultAsset->transcription_id = $transcription->id;
        $surveyStepResultAsset->save();
    }
}
