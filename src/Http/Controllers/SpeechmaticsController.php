<?php

namespace Twoavy\EvaluationTool\Http\Controllers;

use App\Http\Controllers\Controller;
use Curl\Curl;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Twoavy\EvaluationTool\Models\EvaluationToolAudioTranscription;
use Twoavy\EvaluationTool\Models\EvaluationToolSurveyStepResultAsset;
use Twoavy\EvaluationTool\Traits\EvaluationToolResponse;

class SpeechmaticsController extends Controller
{
    use EvaluationToolResponse;

    const SPEECHMATICS_SERVICE_NAME = "speechmatics";

    public function __construct()
    {
        $this->middleware("auth:api");

        $this->disk = Storage::disk("evaluation_tool_audio");

        if (!env('SPEECH_TO_TEXT_SERVICE', false) || env('SPEECH_TO_TEXT_SERVICE') != "speechmatics") {
            abort(409, "service not configured");
        }

        // Speechmatics Config
        $this->speechMaticsEndpoint = env('SPEECHMATICS_API_URL', "https://trial.asr.api.speechmatics.com/v2/jobs/");
        $this->speechMaticsApiKey   = env('SPEECHMATICS_API_KEY');
        $this->speechMaticsConfig   = [
            "type"                 => "transcription",
            "transcription_config" => [
                "language" => "de"
            ]
        ];

        $this->curl = new Curl();
        $this->curl->setHeader('Content-Type', 'multipart/form-data');
        $this->curl->setHeader('Authorization', 'Bearer ' . $this->speechMaticsApiKey);
    }

    public function getTranscription(EvaluationToolSurveyStepResultAsset $resultAsset): JsonResponse
    {
        if (!$resultAsset->audio_transcription) {
            $this->sendJob($resultAsset);
        } else {
            if ($resultAsset->audio_transcription->transaction_id) {
                if ($resultAsset->audio_transcription->status != "done") {
                    $this->getStatus($resultAsset);
                }

                $resultAsset->audio_transcription->refresh();
                if ($resultAsset->audio_transcription->status == "done") {
                    $this->retrieveTranscription($resultAsset);
                }
            }
        }

        $resultAsset->refresh();
        return $this->showOne($resultAsset->audio_transcription);
    }

    public function sendJob(EvaluationToolSurveyStepResultAsset $resultAsset)
    {
        $filePath = $this->disk->path($resultAsset->filename);

        $post_data = [
            'data_file' => new \CurlFile($filePath),
            'config'    => json_encode($this->speechMaticsConfig)
        ];

        $this->curl->post($this->speechMaticsEndpoint, $post_data);

        if ($this->curl->error) {
            return json_encode(['error' => 'Error: ' . $this->curl->error_code . ': ' . $this->curl->error_message]);
        }

        $response = json_decode($this->curl->response);

        if (isset($response->id)) {
            if (!$resultAsset->audio_transcription) {
                $audioTranscription                 = new EvaluationToolAudioTranscription();
                $audioTranscription->service        = self::SPEECHMATICS_SERVICE_NAME;
                $audioTranscription->transaction_id = $response->id;
                $audioTranscription->result_payload = $response;
                $audioTranscription->status         = "sent";
                $audioTranscription->save();

                $resultAsset->transcription_id = $audioTranscription->id;
                $resultAsset->save();
            }
            return $audioTranscription;
        }

        return false;
    }

    public function getStatus(EvaluationToolSurveyStepResultAsset $resultAsset)
    {
        $url = $this->speechMaticsEndpoint . $resultAsset->audio_transcription->transaction_id;
        $this->curl->get($url);

        if ($this->curl->error) {
            return json_encode(['error' => 'Error: ' . $this->curl->error_code . ': ' . $this->curl->error_message]);
        }

        $response = json_decode($this->curl->response);

        if ($response->job->status) {
            $status                                   = $response->job->status;
            $resultAsset->audio_transcription->status = $status;
            $resultAsset->audio_transcription->save();
        }

        return $resultAsset->audio_transcription;
    }

    public function retrieveTranscription(EvaluationToolSurveyStepResultAsset $resultAsset)
    {
        $url = $this->speechMaticsEndpoint . $resultAsset->audio_transcription->transaction_id . "/transcript";
        $this->curl->get($url);

        if ($this->curl->error) {
            return json_encode(['error' => 'Error: ' . $this->curl->error_code . ': ' . $this->curl->error_message]);
        }

        $response = json_decode($this->curl->response);

        $resultAsset->audio_transcription->result_payload = $response;
        $resultAsset->audio_transcription->save();

        $textElements = [];
        foreach ($response->results as $result) {
            $textElements[] = $result->alternatives[0]->content;
        }

        // handle commas and periods
        $text = implode(" ", $textElements);
        $text = str_replace(" ,", ",", $text);
        $text = str_replace(" .", ".", $text);
        $text = str_replace(" !", "!", $text);
        $text = str_replace(" ?", "?", $text);

        // write api transcription
        $resultAsset->audio_transcription->api_transcription = $text;
        $resultAsset->audio_transcription->save();

        return $this->showOne($resultAsset->audio_transcription);
    }
}
