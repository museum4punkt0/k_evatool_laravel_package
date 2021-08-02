<?php

namespace Twoavy\EvaluationTool;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class EvaluationToolSurveyController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json("test");
    }
}
