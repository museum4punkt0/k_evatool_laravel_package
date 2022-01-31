<?php

namespace Twoavy\EvaluationTool\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Twoavy\EvaluationTool\Http\Requests\EvaluationToolSettingStoreRequest;
use Twoavy\EvaluationTool\Models\EvaluationToolSetting;
use Twoavy\EvaluationTool\Traits\EvaluationToolResponse;

class EvaluationToolSettingController extends Controller
{
    use EvaluationToolResponse;

    public function __construct()
    {
        $this->middleware("auth:api");
        $this->disk = Storage::disk("evaluation_tool_settings_assets");
        $this->uploadDisk = Storage::disk("evaluation_tool_uploads");
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $settings = EvaluationToolSetting::all();
        return $this->showAll($settings);
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreEvaluationToolSettingRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(EvaluationToolSettingStoreRequest $request)
    {
        $setting = new EvaluationToolSetting();
        $setting->fill($request->all());
        $setting->save();

        return $this->showOne($setting->refresh());
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\EvaluationToolSetting  $evaluationToolSetting
     * @return \Illuminate\Http\Response
     */
    public function show(EvaluationToolSetting $setting)
    {
            return $this->showOne($setting);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateEvaluationToolSettingRequest  $request
     * @param  \App\Models\EvaluationToolSetting  $evaluationToolSetting
     * @return \Illuminate\Http\Response
     */
    public function update(EvaluationToolSettingStoreRequest $request, EvaluationToolSetting $setting)
    {
        $setting->fill($request->all());
        $setting->save();

        return $this->showOne($setting->refresh());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\EvaluationToolSetting  $evaluationToolSetting
     * @return \Illuminate\Http\Response
     */
    public function destroy(EvaluationToolSetting $setting)
    {
        // TODO: check if a survey uses settings
        return $this->errorResponse("settings cannot be deleted, because i still have not yet checked if they are in use or not ".$setting->id, 409);

        $setting->delete();
        return $this->showOne($setting->refresh());
    }
}
