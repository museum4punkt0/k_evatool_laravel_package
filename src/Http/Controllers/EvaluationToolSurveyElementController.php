<?php

namespace Twoavy\EvaluationTool\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Twoavy\EvaluationTool\Models\EvaluationToolSurveyElement;

class EvaluationToolSurveyElementController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $surveyElements = EvaluationToolSurveyElement::all();
        return response()->json($surveyElements);
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
     * @param  \App\Models\EvaluationToolSurveyElement  $evaluationToolSurveyElement
     * @return \Illuminate\Http\Response
     */
    public function show(EvaluationToolSurveyElement $evaluationToolSurveyElement)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\EvaluationToolSurveyElement  $evaluationToolSurveyElement
     * @return \Illuminate\Http\Response
     */
    public function edit(EvaluationToolSurveyElement $evaluationToolSurveyElement)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\EvaluationToolSurveyElement  $evaluationToolSurveyElement
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, EvaluationToolSurveyElement $evaluationToolSurveyElement)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\EvaluationToolSurveyElement  $evaluationToolSurveyElement
     * @return \Illuminate\Http\Response
     */
    public function destroy(EvaluationToolSurveyElement $evaluationToolSurveyElement)
    {
        //
    }
}
