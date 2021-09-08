<?php

namespace Twoavy\EvaluationTool\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Twoavy\EvaluationTool\Models\EvaluationToolAsset;
use Twoavy\EvaluationTool\Traits\EvaluationToolResponse;

class EvaluationToolAssetController extends Controller
{
    use EvaluationToolResponse;

    public function __construct()
    {
        $this->disk       = Storage::disk("evaluation_tool_assets");
        $this->demoDisk   = Storage::disk("evaluation_tool_demo_assets");
        $this->uploadDisk = Storage::disk("evaluation_tool_uploads");
    }

    /**
     * Retrieve a list of all assets
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $assets = EvaluationToolAsset::all();
        return $this->showAll($assets);
    }

    /**
     *  Retrieve a single asset
     *
     * @param EvaluationToolAsset $asset
     * @return JsonResponse
     */
    public function show(EvaluationToolAsset $asset): JsonResponse
    {
        return $this->showOne($asset);
    }

    public function createSampleAssets()
    {
        $asset = new EvaluationToolAsset();

        $filename     = "demo_video.mp4";
        $filenameSlug = Str::slug(pathinfo($filename, 8), "_") . "." . strtolower(pathinfo($filename, 4));

        $asset->filename = $filenameSlug;
        $asset->hash     = hash_file('md5', $this->demoDisk->path($filename));
        $asset->size     = $this->demoDisk->size($filename);

        $this->disk->put($filenameSlug, $this->demoDisk->get($filename));

        $asset->save();
    }

    public function createTusAsset($tusData)
    {
//        touch("create.txt");
        $asset = new EvaluationToolAsset();

        $filename     = $tusData["name"];
        $filenameSlug = Str::slug(pathinfo($filename, 8), "_") . "." . strtolower(pathinfo($filename, 4));

        $asset->filename = $filenameSlug;
        $asset->hash     = hash_file('md5', $this->uploadDisk->path($filename));
        $asset->size     = $this->uploadDisk->size($filename);

        $this->disk->put($filenameSlug, $this->uploadDisk->get($filename));

        $this->uploadDisk->delete($filename);

        $asset->save();
    }
}
