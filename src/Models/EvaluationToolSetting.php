<?php

namespace Twoavy\EvaluationTool\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
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

    // setting asset keys providing urls
    public $settingsAssetUrlKeys = ['logoImage', 'iconImage', 'backgroundImage'];

    public function surveys(): HasMany
    {
        return $this->hasMany(EvaluationToolSurvey::class, "setting_id");
    }

    public function created_by_user(): HasOne
    {
        return $this->hasOne(User::class, "id", "created_by");
    }

    public function updated_by_user(): HasOne
    {
        return $this->hasOne(User::class, "id", "updated_by");
    }

    public function deleted_by_user(): HasOne
    {
        return $this->hasOne(User::class, "id", "deleted_by");
    }


    /**
     *  save asset's paths always as file basename
     *
     * @param $settings
     * @return void
     */
    public function setSettingsAttribute($settings)
    {
        $settingsArray = (array)$settings;

        foreach ($this->settingsAssetUrlKeys as $url) {
            $settingsArray[$url] = basename($settingsArray[$url]);
        }

        $this->attributes['settings'] = json_encode($settingsArray);
    }
}
