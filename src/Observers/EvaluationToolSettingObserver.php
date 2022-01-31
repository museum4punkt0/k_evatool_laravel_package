<?php

namespace Twoavy\EvaluationTool\Observers;

use Twoavy\EvaluationTool\Models\EvaluationToolSetting;

class EvaluationToolSettingObserver
{
    /**
     * @param EvaluationToolSetting $setting
     * @return void
     */
    public function creating(EvaluationToolSetting $setting)
    {
        if(EvaluationToolSetting::all()->count() == 0){
            $setting->default = true;
        }
    }
}
