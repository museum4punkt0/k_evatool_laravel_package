<?php

namespace Twoavy\EvaluationTool\Http\Controllers;


use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Twoavy\EvaluationTool\Models\EvaluationToolSurveyStepResult;

class EvaluationToolSurveyStepResultController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $surveyStepResults = EvaluationToolSurveyStepResult::all();
        return response()->json($surveyStepResults);
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
     * @param  \App\Models\EvaluationToolSurveyStepResult  $evaluationToolSurveyStepResult
     * @return \Illuminate\Http\Response
     */
    public function show(EvaluationToolSurveyStepResult $evaluationToolSurveyStepResult)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\EvaluationToolSurveyStepResult  $evaluationToolSurveyStepResult
     * @return \Illuminate\Http\Response
     */
    public function edit(EvaluationToolSurveyStepResult $evaluationToolSurveyStepResult)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\EvaluationToolSurveyStepResult  $evaluationToolSurveyStepResult
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, EvaluationToolSurveyStepResult $evaluationToolSurveyStepResult)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\EvaluationToolSurveyStepResult  $evaluationToolSurveyStepResult
     * @return \Illuminate\Http\Response
     */
    public function destroy(EvaluationToolSurveyStepResult $evaluationToolSurveyStepResult)
    {
        //
    }
}
