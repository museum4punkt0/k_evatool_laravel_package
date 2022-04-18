<?php

namespace Twoavy\EvaluationTool\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Twoavy\EvaluationTool\Transformers\EvaluationToolSettingTransformer;

class EvaluationToolSetting extends Model
{
    use HasFactory, SoftDeletes;

    public $transformer = EvaluationToolSettingTransformer::class;

    protected $fillable = [
        "default",
        "name",
        "settings",
    ];

    protected $casts = [
        "default"  => "boolean",
        "settings" => "object"
    ];

    protected $withCount = ["surveys"];

    public function surveys(): HasMany
    {
        return $this->hasMany(EvaluationToolSurvey::class, "setting_id");
    }
}
