<?php

namespace Twoavy\EvaluationTool\Http\Controllers;

use App\Http\Controllers\Controller;
use getID3;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\Image\Exceptions\InvalidManipulation;
use Spatie\Image\Image;
use Twoavy\EvaluationTool\Http\Requests\EvaluationToolAudioTranscriptionStoreRequest;
use Twoavy\EvaluationTool\Models\EvaluationToolAsset;
use Twoavy\EvaluationTool\Models\EvaluationToolAudioTranscription;
use Twoavy\EvaluationTool\Models\EvaluationToolSurveyStepResultAsset;
use Twoavy\EvaluationTool\Traits\EvaluationToolResponse;

class EvaluationToolAudioTranscriptionController extends Controller
{
    use EvaluationToolResponse;

    public function __construct()
    {

        $this->middleware("auth:api");
    }

    /**
     * Retrieve a list of all assets
     *
     * @param EvaluationToolSurveyStepResultAsset $resultAsset
     * @param EvaluationToolAudioTranscriptionStoreRequest $request
     * @return JsonResponse
     */
    public function setManualTranscription(EvaluationToolSurveyStepResultAsset $resultAsset, EvaluationToolAudioTranscriptionStoreRequest $request):
    JsonResponse
    {
        if (!$resultAsset->audio_transcription) {
            $audioTranscription = new EvaluationToolAudioTranscription();
            $audioTranscription->save();

            $resultAsset->transcription_id = $audioTranscription->id;
            $resultAsset->save();
        }

        $resultAsset->refresh();
        $resultAsset->audio_transcription->manual_transcription = $request->manual_transcription;
        $resultAsset->audio_transcription->save();

        return $this->showOne($resultAsset->audio_transcription);

    }
}
