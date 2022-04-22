<?php

namespace Twoavy\EvaluationTool\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Settings;
use StdClass;
use Twoavy\EvaluationTool\Http\Requests\EvaluationToolSettingStoreRequest;
use Twoavy\EvaluationTool\Http\Requests\EvaluationToolSettingUpdateRequest;
use Twoavy\EvaluationTool\Models\EvaluationToolSetting;
use Twoavy\EvaluationTool\Traits\EvaluationToolResponse;

class EvaluationToolSettingController extends Controller
{
    use EvaluationToolResponse;

    public function __construct()
    {
        $this->middleware("auth:api");
        $this->disk       = Storage::disk("evaluation_tool_settings_assets");
        $this->uploadDisk = Storage::disk("evaluation_tool_uploads");
    }

    /**
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $settings = EvaluationToolSetting::all();
        return $this->showAll($settings);
    }

    /**
     * Display the specified resource.
     *
     * @param EvaluationToolSetting $setting
     * @return JsonResponse
     */
    public function show(EvaluationToolSetting $setting): JsonResponse
    {
        return $this->showOne($setting);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param EvaluationToolSettingStoreRequest $request
     * @return JsonResponse
     */
    public function store(EvaluationToolSettingStoreRequest $request): JsonResponse
    {
        $setting = new EvaluationToolSetting();
        $setting->fill($request->all());
        $setting->settings = new StdClass;
        $setting->save();

        return $this->showOne($setting->refresh());
    }

    /**
     * Update the specified resource in storage.
     *
     * @param EvaluationToolSettingUpdateRequest $request
     * @param EvaluationToolSetting $setting
     * @return JsonResponse
     */
    public function update(EvaluationToolSettingUpdateRequest $request, EvaluationToolSetting $setting): JsonResponse
    {
        $setting->fill($request->all());
        $setting->save();

        return $this->showOne($setting->refresh());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param EvaluationToolSetting $setting
     * @return JsonResponse
     */
    public function destroy(EvaluationToolSetting $setting): JsonResponse
    {
        if ($setting->surveys()->count() > 0) {
            return $this->errorResponse("settings cannot be deleted, because it is still in use", 409);
        }

        $setting->delete();

        return $this->showOne($setting->refresh());
    }

    /**
     *
     *  Store settings assets
     *
     * @param Request $request
     * @param EvaluationToolSetting $setting
     * @return JsonResponse
     */
    public function storeAsset(Request $request, EvaluationToolSetting $setting): JsonResponse
    {
        $request->validate([
            'file' => 'image',
        ]);

        $assetMeta = json_decode($request->assetMeta, true);
        $settings = json_decode(json_encode($setting['settings']), true);

        // asset subtype validation
        $validSubTypes = ['logo', 'icon', 'background'];
        if (!in_array($assetMeta['subType'], $validSubTypes)) {
            return $this->errorResponse("the given asset subtype is invalid", 422);
        }

        // generate asset file name and store file to the disk
        $pathInfo = pathinfo($request->name);
        $fileName = Str::slug($pathInfo['filename'], '_', 'de')
                    . '_' . hash('md5', time())
                    . '.' . $pathInfo['extension'];

        $this->disk->put($fileName, $request->file->getContent());

        // remove existing asset file
        $settingsAssetKey = $assetMeta['subType'] . 'Image';
        if (isset($settings[$settingsAssetKey])) {
            $this->disk->delete(basename($settings[$settingsAssetKey]));
        }

        // update setting
        $setting->settings = array_merge($settings, [$settingsAssetKey => $this->disk->url($fileName)]);
        $setting->save();

        return $this->successResponse($request->allFiles());
    }
}
