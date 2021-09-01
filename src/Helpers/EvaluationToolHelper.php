<?php

namespace Twoavy\EvaluationTool\Helpers;

class EvaluationToolHelper
{
    public static function transformModel($model, $removeDataKey = true): ?array
    {
        if (isset($model->transformer)) {
            $transformer    = $model->transformer;
            $transformation = fractal($model, new $transformer);
            if ($removeDataKey) {
                return $transformation->toArray()["data"];
            }
            return $transformation->toArray();
        }
        return $model;
    }
}
