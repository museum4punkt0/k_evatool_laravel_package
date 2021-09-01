<?php

namespace Twoavy\EvaluationTool\Helpers;

use Illuminate\Http\Request;

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

    public static function reverseTransform(Request $request, $transformer) {
        $transformedInput = [];

        foreach ($request->request->all() as $input => $value) {
            if($transformer::originalAttribute($input)) {
                $transformedInput[$transformer::originalAttribute($input)] = $value;
            }
        }

        $request->replace($transformedInput);
    }
}
