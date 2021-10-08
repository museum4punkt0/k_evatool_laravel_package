<?php

namespace Twoavy\EvaluationTool\Observers;

use Twoavy\EvaluationTool\Models\EvaluationToolAsset;

class EvaluationToolAssetObserver
{
    /**
     * @param EvaluationToolAsset $asset
     */
    public function creating(EvaluationToolAsset $asset)
    {
        if (isset(request()->user()->id)) {
            $asset->created_by = request()->user()->id;
            $asset->updated_by = request()->user()->id;
        }
    }

    public function updating(EvaluationToolAsset $asset)
    {
        if (isset(request()->user()->id)) {
            $asset->updated_by = request()->user()->id;
        }
    }

    /**
     * @param EvaluationToolAsset $asset
     */
    public function deleting(EvaluationToolAsset $asset)
    {
        if (isset(request()->user()->id)) {
            $asset->deleted_by = request()->user()->id;
            $asset->save();
        }
    }
}
