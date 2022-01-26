<?php

namespace Twoavy\EvaluationTool\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;
use Twoavy\EvaluationTool\Helpers\EvaluationToolHelper;
use Twoavy\EvaluationTool\Transformers\EvaluationToolAudioTranscriptionTransformer;

class EvaluationToolAudioTranscriptionStoreRequest extends FormRequest
{

    public function __construct(Request $request)
    {
        parent::__construct();
        EvaluationToolHelper::reverseTransform($request, EvaluationToolAudioTranscriptionTransformer::class);

    }

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            "manual_transcription" => "required|min:1|max:1000"
        ];
    }
}
