<?php

namespace App\Http\Controllers;

namespace Twoavy\EvaluationTool\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Twoavy\EvaluationTool\Models\EvaluationToolSurveyLocalization;

class EvaluationToolSurveyLocalizationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $surveyLocalizations = EvaluationToolSurveyLocalization::all();
        return response()->json($surveyLocalizations);
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
     * @param  \App\Models\EvaluationToolSurveyLocalization  $evaluationToolSurveyLocalization
     * @return \Illuminate\Http\Response
     */
    public function show(EvaluationToolSurveyLocalization $evaluationToolSurveyLocalization)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\EvaluationToolSurveyLocalization  $evaluationToolSurveyLocalization
     * @return \Illuminate\Http\Response
     */
    public function edit(EvaluationToolSurveyLocalization $evaluationToolSurveyLocalization)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\EvaluationToolSurveyLocalization  $evaluationToolSurveyLocalization
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, EvaluationToolSurveyLocalization $evaluationToolSurveyLocalization)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\EvaluationToolSurveyLocalization  $evaluationToolSurveyLocalization
     * @return \Illuminate\Http\Response
     */
    public function destroy(EvaluationToolSurveyLocalization $evaluationToolSurveyLocalization)
    {
        //
    }
}
