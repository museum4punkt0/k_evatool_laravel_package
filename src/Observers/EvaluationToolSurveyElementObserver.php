<?php

namespace Twoavy\EvaluationTool\Observers;

use Twoavy\EvaluationTool\Models\EvaluationToolAsset;
use Twoavy\EvaluationTool\Models\EvaluationToolSurveyElement;
use Twoavy\EvaluationTool\Models\EvaluationToolSurveyElementType;

class EvaluationToolSurveyElementObserver
{
    /**
     * @param EvaluationToolSurveyElement $surveyElement
     * @return void
     */
    public function creating(EvaluationToolSurveyElement $surveyElement)
    {
        if (request()->has("survey_element_type")) {
            $surveyElement->survey_element_type_id = EvaluationToolSurveyElementType::where('key', request()->survey_element_type)
                ->first()
                ->id;
        }
        if (isset(request()->user()->id)) {
            $surveyElement->created_by = request()->user()->id;
            $surveyElement->updated_by = request()->user()->id;
        }
    }

    /**
     * @param EvaluationToolSurveyElement $surveyElement
     * @return void
     */
    public function created(EvaluationToolSurveyElement $surveyElement)
    {
        $this->assignAssets($surveyElement);
    }

    /**
     * @return void
     */
    public function updating(EvaluationToolSurveyElement $surveyElement)
    {
        if (isset(request()->user()->id)) {
            $surveyElement->updated_by = request()->user()->id;
        }
    }

    /**
     * @param EvaluationToolSurveyElement $surveyElement
     * @return void
     */
    public function updated(EvaluationToolSurveyElement $surveyElement)
    {
        $this->assignAssets($surveyElement);
    }

    /**
     * @return void
     */
    public function deleting(EvaluationToolSurveyElement $surveyElement)
    {
        if (isset(request()->user()->id)) {
            $surveyElement->deleted_by = request()->user()->id;
            $surveyElement->save();
        }
    }

    public function assignAssets($surveyElement)
    {
        // yay nay
        if ($surveyElement->survey_element_type_id == 5) {
            $surveyElement->assets()->detach();
            if (isset($surveyElement->params->assetIds) && is_array($surveyElement->params->assetIds) && !empty($surveyElement->params->assetIds)) {
                $surveyElement->assets()->attach($surveyElement->params->assetIds);
            }
        }

        // video
        if ($surveyElement->survey_element_type_id == 7) {
            $surveyElement->assets()->detach();
            if (isset($surveyElement->params->videoAssetId) && EvaluationToolAsset::find($surveyElement->params->videoAssetId)) {
                $surveyElement->assets()->attach($surveyElement->params->videoAssetId);
            }
        }
    }
}
