<?php

namespace Twoavy\EvaluationTool\Http\Controllers;

use App\Http\Controllers\Controller;
use Google\Cloud\Speech\V1\RecognitionConfig;
use Google\Cloud\Speech\V1\RecognitionConfig\AudioEncoding;
use Google\Cloud\Speech\V1\StreamingRecognitionConfig;
use Illuminate\Http\JsonResponse;
use Twoavy\EvaluationTool\Traits\EvaluationToolResponse;

class EvaluationToolSpeechToTextController extends Controller
{
    use EvaluationToolResponse;

    public function __construct()
    {
        $this->recognitionConfig = new RecognitionConfig();
        $this->config = new StreamingRecognitionConfig();
        $this->config->setConfig($this->recognitionConfig);
    }


    public function getTextFromAudioFile($audioAssetId, $language = "de-DE"): JsonResponse
    {
        $this->recognitionConfig->setEncoding(AudioEncoding::FLAC);
        $this->recognitionConfig->setSampleRateHertz(44100);
        $this->recognitionConfig->setLanguageCode($language);

        $audioResource = fopen('path/to/audio.flac', 'r');

        $responses = $speechClient->recognizeAudioStream($this->config, $audioResource);

        foreach ($responses as $element) {
            // doSomethingWith($element);
        }
    }
}
