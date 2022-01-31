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

    protected $casts = [
        "default" => "boolean",
        "settings" => "object"
    ];
    protected $fillable = [
        "default",
        "name",
        "settings",
    ];
    public $transformer = EvaluationToolSettingTransformer::class;

    public function survey(): HasMany
    {
        return $this->hasMany(EvaluationToolSurvey::class);
    }
}
