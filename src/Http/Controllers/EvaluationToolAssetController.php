<?php

namespace Twoavy\EvaluationTool\Http\Controllers;

use App\Http\Controllers\Controller;
use getID3;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\Image\Image;
use Twoavy\EvaluationTool\Models\EvaluationToolAsset;
use Twoavy\EvaluationTool\Traits\EvaluationToolResponse;

class EvaluationToolAssetController extends Controller
{
    use EvaluationToolResponse;

    public function __construct()
    {

        $this->middleware("auth:api");

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
        $asset->mime     = mime_content_type($this->demoDisk->path($filename));
        $asset->meta     = self::getFileMetaData($this->demoDisk->path($filename));

        $this->disk->put($filenameSlug, $this->demoDisk->get($filename));

        $asset->save();
    }

    public function createTusAsset($tusData)
    {
        $asset = new EvaluationToolAsset();

        $filename     = $tusData["name"];
        $filenameSlug = Str::slug(pathinfo($filename, 8), "_") . "." . strtolower(pathinfo($filename, 4));

        $asset->filename = $filenameSlug;
        $asset->hash     = hash_file('md5', $this->uploadDisk->path($filename));
        $asset->size     = $this->uploadDisk->size($filename);
        $asset->mime     = mime_content_type($this->uploadDisk->path($filename));

        $this->disk->put($filenameSlug, $this->uploadDisk->get($filename));

        $this->uploadDisk->delete($filename);

        $filePath = $this->disk->path($filename);
        $asset->meta = self::getFileMetaData($filePath);

        $asset->save();

        /* PREVIEW IMAGE  */
        @mkdir($this->disk->path("preview"));
        Image::load($filePath)
            ->width(800)
            ->optimize()
            ->quality(60)
            ->save($this->disk->path("preview/" . $filename));

        /* THUMBNAIL IMAGE */
        @mkdir($this->disk->path("thumbnail"));
        Image::load($filePath)
            ->width(200)
            ->optimize()
            ->quality(60)
            ->save($this->disk->path("thumbnail/" . $filename));
    }

    public static function getFileMetaData($path): array
    {
        $getId3   = new getID3();
        $metaData = $getId3->analyze($path);

        $metaDataPrepared = [];

        if (isset($metaData["video"])) {
            $metaDataPrepared["video"] = $metaData["video"];
        }

        if (isset($metaData["audio"])) {
            $metaDataPrepared["audio"] = $metaData["audio"];
        }

        return $metaDataPrepared;
    }
}
