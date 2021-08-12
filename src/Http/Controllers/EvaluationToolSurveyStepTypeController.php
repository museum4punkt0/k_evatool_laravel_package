<?php

namespace Twoavy\EvaluationTool\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Twoavy\EvaluationTool\Models\EvaluationToolSurveyStepType;

class EvaluationToolSurveyStepTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $surveyStepTypes = EvaluationToolSurveyStepType::all();
        return response()->json($surveyStepTypes);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\EvaluationToolSurveyStepType  $evaluationToolSurveyStepType
     * @return \Illuminate\Http\Response
     */
    public function show(EvaluationToolSurveyStepType $evaluationToolSurveyStepType)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\EvaluationToolSurveyStepType  $evaluationToolSurveyStepType
     * @return \Illuminate\Http\Response
     */
    public function edit(EvaluationToolSurveyStepType $evaluationToolSurveyStepType)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\EvaluationToolSurveyStepType  $evaluationToolSurveyStepType
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, EvaluationToolSurveyStepType $evaluationToolSurveyStepType)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\EvaluationToolSurveyStepType  $evaluationToolSurveyStepType
     * @return \Illuminate\Http\Response
     */
    public function destroy(EvaluationToolSurveyStepType $evaluationToolSurveyStepType)
    {
        //
    }
}
