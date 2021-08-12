<?php

namespace Twoavy\EvaluationTool\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Twoavy\EvaluationTool\Models\EvaluationToolSurveyLanguage;

class EvaluationToolSurveyLanguageController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $surveyLanguages = EvaluationToolSurveyLanguage::all();
        return response()->json($surveyLanguages);
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
     * @param  \App\Models\EvaluationToolSurveyLanguage  $evaluationToolSurveyLanguage
     * @return \Illuminate\Http\Response
     */
    public function show(EvaluationToolSurveyLanguage $evaluationToolSurveyLanguage)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\EvaluationToolSurveyLanguage  $evaluationToolSurveyLanguage
     * @return \Illuminate\Http\Response
     */
    public function edit(EvaluationToolSurveyLanguage $evaluationToolSurveyLanguage)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\EvaluationToolSurveyLanguage  $evaluationToolSurveyLanguage
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, EvaluationToolSurveyLanguage $evaluationToolSurveyLanguage)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\EvaluationToolSurveyLanguage  $evaluationToolSurveyLanguage
     * @return \Illuminate\Http\Response
     */
    public function destroy(EvaluationToolSurveyLanguage $evaluationToolSurveyLanguage)
    {
        //
    }
}
