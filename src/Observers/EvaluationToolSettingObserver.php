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
        // TODO: check why creating never gets called
        if(EvaluationToolSetting::all()->count() == 0){
            $setting->default = true;
        }
    }
}
