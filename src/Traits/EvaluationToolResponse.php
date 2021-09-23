<?php

namespace Twoavy\EvaluationTool\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;

trait EvaluationToolResponse
{
    protected function successResponse($data, $code, $systemCode = false): JsonResponse
    {
        if ($systemCode) {
            $data["code"] = $systemCode;
        }
        return response()->json($data, $code);
    }

    protected function userAccessDenied(): JsonResponse
    {
        return $this->errorResponse("access denied", 403);
    }

    protected function errorResponse($message, $code, $systemCode = false): JsonResponse
    {
        $response = ["error" => $message, "code" => $code];
        if ($systemCode) {
            $response["code"] = $systemCode;
        }
        return response()->json($response, $code);
    }

    protected function responseWithCode($code, $responseCode, $additionalData = false): JsonResponse
    {
        $codes = json_decode(file_get_contents(app_path('evaluation_tool_response_codes.json')));
        $data  = [
            "code"    => $code,
            "message" => $codes->{$code}
        ];
        if ($additionalData) {
            $data["data"] = $additionalData;
        }
        return response()->json($data, $responseCode);
    }

    protected function abortWithCode($code, $responseCode)
    {
        $codes = json_decode(file_get_contents(app_path('evaluation_tool_response_codes.json')));
        abort($responseCode, $codes->{$code});
    }

    protected function errorWithCode($code, $responseCode, $additionalData = false): JsonResponse
    {
        $codes = json_decode(file_get_contents(app_path('evaluation_tool_response_codes.json')));
        $data  = [
            "code"  => $code,
            "error" => $codes->{$code}
        ];
        if ($additionalData) {
            $data["data"] = $additionalData;
        }
        return response()->json($data, $responseCode);
    }

    protected function showAll(Collection $collection, $code = 200, $transformerOverride = false, $paginate = true, $emitResponse = true)
    {
        // handle empty collection
        if ($collection->isEmpty()) {
            return $this->successResponse([
                "data" => $collection,
                "meta" => [
                    "pagination" => [
                        "links" => []
                    ]
                ]
            ], $code);
        }

        $transformer = false;
        if ($collection->first() && isset($collection->first()->transformer)) {
            $transformer = $collection->first()->transformer;
        }

        if ($transformerOverride) {
            $transformer = $transformerOverride;
        }

        if ($transformer) {
            $collection = $this->filterData($collection, $transformer);
            $collection = $this->sortData($collection, $transformer);
            if ($paginate) {
                $collection = $this->paginate($collection);
            }
            $collection = $this->transformData($collection, $transformer);

            if($emitResponse) {
                return $this->successResponse($collection, $code);
            }

            return $collection["data"];
        }

        if($emitResponse) {
            return $this->successResponse($this->paginate($collection), $code);
        }

        return $collection["data"];
    }

    protected function filterData($collection, $transformer)
    {
        foreach (request()->query() as $query => $value) {
            $attribute = $query;
            if ($transformer) {
                $attribute = $transformer::originalAttribute($attribute);
            }
            if (isset($attribute, $value)) {
                $collection = $collection->where($attribute, $value);
            }
        }

        return $collection;
    }

    protected function sortData(Collection $collection, $transformer): Collection
    {
        if (request()->has('sort_by')) {
            $attribute = request()->sort_by;
            if ($transformer) {
                $attribute = $transformer::originalAttribute(request()->sort_by);
            }
            if (request()->has('sort_dir') && request()->has('sort_dir') == 'desc') {
                $collection = $collection->sortByDesc($attribute);
            } else {
                $collection = $collection->sortBy($attribute);
            }
        }
        return $collection;
    }

    protected function paginate($collection, $perPage = 25): LengthAwarePaginator
    {
        $rules = [
            'per_page' => 'integer|min:2|max:1000',
            'perPage'  => 'integer|min:2|max:1000'
        ];

        Validator::validate(request()->all(), $rules);

        $page = LengthAwarePaginator::resolveCurrentPage();

        // get model-based per page value based on first object on collection
        if (is_object($collection->first()) && $model = get_class($collection->first())) {
            $model = new $model;
            if (method_exists($model, 'getPerPage')) {
                $perPage = $model->getPerPage();
            }
        }

        if (request()->has('per_page')) {
            $perPage = (int)request()->per_page;
        }

        if (request()->has('perPage')) {
            $perPage = (int)request()->perPage;
        }

        if (request()->has('all')) {
            $perPage = count($collection);
        }

        $results = $collection->slice(($page - 1) * $perPage, $perPage)->values();

        $paginated = new LengthAwarePaginator($results, $collection->count(), $perPage, $page, [
            'path' => LengthAwarePaginator::resolveCurrentPath(),
        ]);

        $paginated->appends(request()->all());

        return $paginated;
    }

    protected function showOne(Model $instance, $code = 200, $transformerOverride = false): JsonResponse
    {
        if (isset($instance->transformer)) {
            $transformer = $instance->transformer;
            if ($transformerOverride) {
                $transformer = $transformerOverride;
            }
            $data = $this->transformData($instance, $transformer);
            return $this->successResponse($data, $code);
        }
        return $this->successResponse(["data" => $instance], $code);
    }

    protected function transformData($data, $transformer, $removeDataKey = false): array
    {
        $transformation = fractal($data, new $transformer);
        if ($removeDataKey) {
            $data = $transformation->toArray();
            return $data["data"];
        }
        return $transformation->toArray();
    }
}
