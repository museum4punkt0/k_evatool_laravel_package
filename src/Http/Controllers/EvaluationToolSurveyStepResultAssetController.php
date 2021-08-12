<?php

namespace Twoavy\EvaluationTool\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Twoavy\EvaluationTool\Models\EvaluationToolSurveyStepResultAsset;

class EvaluationToolSurveyStepResultAssetController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $surveyStepResultAssets = EvaluationToolSurveyStepResultAsset::all();
        return response()->json($surveyStepResultAssets);
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
     * @param  \App\Models\EvaluationToolSurveyStepResultAsset  $evaluationToolSurveyStepResultAsset
     * @return \Illuminate\Http\Response
     */
    public function show(EvaluationToolSurveyStepResultAsset $evaluationToolSurveyStepResultAsset)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\EvaluationToolSurveyStepResultAsset  $evaluationToolSurveyStepResultAsset
     * @return \Illuminate\Http\Response
     */
    public function edit(EvaluationToolSurveyStepResultAsset $evaluationToolSurveyStepResultAsset)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\EvaluationToolSurveyStepResultAsset  $evaluationToolSurveyStepResultAsset
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, EvaluationToolSurveyStepResultAsset $evaluationToolSurveyStepResultAsset)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\EvaluationToolSurveyStepResultAsset  $evaluationToolSurveyStepResultAsset
     * @return \Illuminate\Http\Response
     */
    public function destroy(EvaluationToolSurveyStepResultAsset $evaluationToolSurveyStepResultAsset)
    {
        //
    }
}
