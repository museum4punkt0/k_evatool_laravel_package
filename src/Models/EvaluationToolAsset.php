<?php

namespace Twoavy\EvaluationTool\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Twoavy\EvaluationTool\Transformers\EvaluationToolAssetTransformer;

class EvaluationToolAsset extends Model
{
    use HasFactory, SoftDeletes;

    // transforms data on api responses
    public $transformer = EvaluationToolAssetTransformer::class;

    // number of items on paginated responses
    protected $perPage = 25;

    // fields that can be mass-assigned via create or fill methods
    protected $fillable = [
        "filename",
        "hash",
        "size",
        "mime"
    ];

    protected $casts = ["meta" => "object"];

    protected $appends = ["urls"];

    public function getUrlsAttribute(): array
    {
        $disk = Storage::disk("evaluation_tool_assets");
        $urls = ["original" => $disk->url($this->filename)];
        if ($disk->exists("preview/" . $this->filename)) {
            $urls["preview"] = $disk->url("preview/" . $this->filename);
        }
        if ($disk->exists("thumbnail/" . $this->filename)) {
            $urls["thumbnail"] = $disk->url("thumbnail/" . $this->filename);
        }
        return $urls;
    }

    public function created_by_user(): HasOne
    {
        return $this->hasOne(User::class, "id", "created_by");
    }

    public function updated_by_user(): HasOne
    {
        return $this->hasOne(User::class, "id", "updated_by");
    }
}
