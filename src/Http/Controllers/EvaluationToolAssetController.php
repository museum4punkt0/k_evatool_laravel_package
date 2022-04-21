<?php

namespace Twoavy\EvaluationTool\Http\Controllers;

use App\Http\Controllers\Controller;
use getID3;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\Image\Exceptions\InvalidManipulation;
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
        $demoFiles = $this->demoDisk->files();

        foreach ($demoFiles as $demoFile) {

            if (substr(basename($demoFile), 0, 1) !== ".") {
                $asset = new EvaluationToolAsset();

                $filename     = $demoFile;
                $filenameSlug = Str::slug(pathinfo($filename, 8), "_") . "." . strtolower(pathinfo($filename, 4));

                $asset->filename = $filenameSlug;
                $asset->hash     = hash_file('md5', $this->demoDisk->path($filename));
                $asset->size     = $this->demoDisk->size($filename);
                $asset->mime     = mime_content_type($this->demoDisk->path($filename));
                $asset->meta     = self::getFileMetaData($this->demoDisk->path($filename));

                $this->disk->put($filenameSlug, $this->demoDisk->get($filename));

                $asset->save();

                $this->createPreview($asset);
            }
        }
    }

    /**
     * @throws FileNotFoundException
     */
    public function createTusAsset($tusData)
    {
        $filename = $tusData["name"];

        $hash         = hash_file('md5', $this->uploadDisk->path($filename));
        $filenameSlug = Str::slug(pathinfo($filename, 8), "_") . "_" . substr($hash, 0, 6) . "." . strtolower(pathinfo($filename, 4));

        if (!$asset = EvaluationToolAsset::withTrashed()->where("hash", $hash)->first()) {
            $asset = new EvaluationToolAsset();
        }

        // restore if asset is trashed
        if ($asset->trashed()) {
            $asset->restore();
        }

        $asset->filename = $filenameSlug;
        $asset->hash     = hash_file('md5', $this->uploadDisk->path($filename));
        $asset->size     = $this->uploadDisk->size($filename);
        $asset->mime     = mime_content_type($this->uploadDisk->path($filename));

        $this->disk->put($filenameSlug, $this->uploadDisk->get($filename));

        $this->uploadDisk->delete($filename);

        $filePath    = $this->disk->path($filenameSlug);
        $asset->meta = self::getFileMetaData($filePath);

        $asset->save();

        /* PREVIEW IMAGE  */
        $this->createPreview($asset);
    }

    public function createPreview(EvaluationToolAsset $asset)
    {
        $filepath = $this->disk->path($asset->filename);
        $filename = $asset->filename;

        if (strpos($asset->mime, "image") !== false) {
            @mkdir($this->disk->path("preview"));
            Image::load($filepath)
                ->width(800)
                ->optimize()
                ->quality(60)
                ->save($this->disk->path("preview/" . $filename));

            /* THUMBNAIL IMAGE */
            @mkdir($this->disk->path("thumbnail"));
            Image::load($filepath)
                ->width(200)
                ->optimize()
                ->quality(60)
                ->save($this->disk->path("thumbnail/" . $filename));
        }
    }

    public static function getFileMetaData($path): array
    {
        $getId3   = new getID3();
        $metaData = $getId3->analyze($path);

        $metaDataPrepared = [];

        if (isset($metaData["video"])) {
            $metaDataPrepared["video"] = $metaData["video"];
            if (isset($metaData["playtime_seconds"])) {
                $metaDataPrepared["playtime_seconds"] = $metaData["playtime_seconds"];
            }
            if (isset($metaData["playtime_string"])) {
                $metaDataPrepared["playtime_string"] = $metaData["playtime_string"];
            }
        }

        if (isset($metaData["audio"])) {
            $metaDataPrepared["audio"] = $metaData["audio"];
        }

        return $metaDataPrepared;
    }

    public function destroy(EvaluationToolAsset $asset, Request $request): JsonResponse
    {
        if ($asset->survey_elements()->count() > 0) {
            return $this->errorResponse("cannot be deleted, asset in use", 409);
        }

        // delete files
        if($request->has("force")) {
            $paths = [
                $asset->filename,
                "preview/" . $asset->filename,
                "thumbnail/" . $asset->filename
            ];

            foreach ($paths as $path) {
                if ($this->disk->exists($path)) {
                    $this->disk->delete($path);
                }
            }
            $asset->forceDelete();
        } else {
            $asset->delete();
        }

        return $this->showOne($asset);
    }
}
