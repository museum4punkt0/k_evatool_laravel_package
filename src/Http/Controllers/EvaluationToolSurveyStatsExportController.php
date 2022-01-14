<?php

namespace Twoavy\EvaluationTool\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Csv;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use StdClass;
use Twoavy\EvaluationTool\Http\Requests\EvaluationToolSurveyStatsDownloadRequest;
use Twoavy\EvaluationTool\Http\Requests\EvaluationToolSurveyStatsExportRequest;
use Twoavy\EvaluationTool\Models\EvaluationToolSurvey;
use Twoavy\EvaluationTool\Models\EvaluationToolSurveyLanguage;
use Twoavy\EvaluationTool\Models\EvaluationToolSurveyStepResult;
use Twoavy\EvaluationTool\Traits\EvaluationToolResponse;

class EvaluationToolSurveyStatsExportController extends Controller
{
    use EvaluationToolResponse;

    public function __construct()
    {
        $this->middleware("auth:api");
        $this->disk = Storage::disk("evaluation_tool_exports");
    }

    public function getStatsExport(EvaluationToolSurvey $survey, EvaluationToolSurveyStatsExportRequest $request): JsonResponse
    {
        $filename = "eva_tool_export_survey_" . $survey->id;

        $results = EvaluationToolSurveyStepResult::whereIn("survey_step_id",
            $survey->survey_steps
                ->pluck("id"))
//                ->orderBy('session_id', 'ASC');
            ->orderBy('answered_at', 'DESC');

        // check for start date
        if ($request->has("start")) {
            $start = Carbon::createFromFormat("Y-m-d", $request->start)->startOfDay();
            $results->where("answered_at", ">=", $start);
            $filename .= '_start_' . $request->start;
        }

        // check for end date
        if ($request->has("end")) {
            $end = Carbon::createFromFormat("Y-m-d", $request->end)->endOfDay();
            $results->where("answered_at", "<=", $end);
            $filename .= '_end_' . $request->end;
        }

        if ($request->has("demo") && $request->demo == true) {
            $results->where("demo", true);
            $filename .= '_demo';
        } else {
            $results->where("demo", false);
        }

        $results->orderBy("answered_at", "DESC");

//        echo $results->toSql();

        $results = $results->get();

        $execute = false;
        if ($request->has("execute")) {
            $execute = true;

            $i = 1;

            if ($request->exportType == "xlsx") {
                $filename .= ".xlsx";

                $spreadsheet = new Spreadsheet();
                $sheet       = $spreadsheet->getActiveSheet();

                $preparedResults = $this->prepareResultsForExcel($results, $survey);

//                echo response()->json($preparedResults)->getContent();

                $r = 1;
                foreach ($preparedResults["headers"] as $resultRow) {
                    $c = 1;
                    foreach ($resultRow as $cellItem) {
                        foreach ($cellItem as $cellSubItem) {
//                            print_r($cellSubItem);
                            $sheet->setCellValueByColumnAndRow($c, $r, $cellSubItem["value"]);
                            if (isset($cellSubItem["span"]) && $cellSubItem["span"] > 1) {
//                                echo "merge " . $cellSubItem["span"] . " " . $r . ":" . $c . ":" . ($c + $cellSubItem["span"] - 1) . PHP_EOL;
                                $sheet->mergeCellsByColumnAndRow($c, $r, ($c + $cellSubItem["span"] - 1), $r);
                                $c = $c + $cellSubItem["span"];
                            } else {
                                $c++;
                            }
                        }
                    }
                    $r++;
//                    $sheet->setCellValue('A' . $i, $result->session_id);
//                    $sheet->setCellValue('B' . $i, $result->survey_step->survey_element_type->key);
//                    $sheet->setCellValue('C' . $i, json_encode($result->result_value));
                }

                foreach ($preparedResults["results"] as $resultRow) {
                    foreach ($resultRow as $resultCell) {
                        $sheet->setCellValueByColumnAndRow($resultCell["position"], $r, $resultCell["value"]);
                    }
                    $r++;
                }

                foreach (range('A', $sheet->getHighestDataColumn()) as $col) {
                    $sheet->getColumnDimension($col)
                        ->setAutoSize(true);
                }

                $writer = new Xlsx($spreadsheet);
                $writer->save($this->disk->path($filename));
            }

            if ($request->exportType == "csv") {
                $filename .= " . csv";

                $spreadsheet = new Spreadsheet();
                $sheet       = $spreadsheet->getActiveSheet();

                foreach ($results as $result) {
                    $sheet->setCellValue('A' . $i, $result->session_id);
                    $sheet->setCellValue('B' . $i, $result->survey_step->survey_element_type->key);
                    $sheet->setCellValue('C' . $i, json_encode($result->result_value));
                    $i++;
                }

                $writer = new Csv($spreadsheet);
                $writer->setDelimiter(';');
                $writer->setEnclosure('"');
                $writer->setLineEnding("\r\n");
                $writer->setSheetIndex(0);

                $writer->save($this->disk->path($filename));
            }

            if ($request->exportType == "json") {
                $filename .= ".json";

                $steps = [];

                $preparedResults = $results->map(function ($result) {
                    $preparedResult             = new StdClass;
                    $preparedResult->session_id = $result->session_id;
                    $preparedResult->type       = $result->survey_step->survey_element_type->key;
                    $preparedResult->value      = $result->result_value;
                    return $preparedResult;
                });

                $jsonContent                     = new StdClass;
                $jsonContent->meta               = new StdClass;
                $jsonContent->meta->stepsCount   = count($steps);
                $jsonContent->meta->resultsCount = $preparedResults->count();
                $jsonContent->steps              = $steps;
                $jsonContent->results            = $preparedResults;

                $this->disk->put($filename, json_encode($jsonContent));
            }

        }

        $responsePayload = new StdClass;
        if ($execute) {
            $responsePayload->format   = $request->exportType;
            $responsePayload->filename = $filename;
//            $responsePayload->url      = $this->disk->url($filename);
            $responsePayload->size = $this->disk->size($filename);
            $responsePayload->mime = $this->disk->mimeType($filename);
            $responsePayload->hash = hash('sha1', $this->disk->get($filename));
        }
        $responsePayload->totalResults = $results->count();

        return $this->successResponse($responsePayload);
    }

    public function downloadStatsExport(EvaluationToolSurveyStatsDownloadRequest $request): JsonResponse
    {
        if (!$this->disk->exists($request->filename)) {
            return $this->errorResponse("file not found (" . $request->filename . ")", 404);
        }

        $hash = hash('sha1', $this->disk->get($request->filename));

        if ($hash != $request->filehash) {
            return $this->errorResponse("filehash incorrect", 409);
        }

        return response()->json(base64_encode($this->disk->get($request->filename)));

    }

    public function prepareResultsForExcel($results, EvaluationToolSurvey $survey): array
    {

        $language = EvaluationToolSurveyLanguage::all()->first();

        $sessionIds = $results->map(function ($result) {
            return $result->only("session_id");
        })->groupBy("session_id")->keys();

//        echo $sessionIds->count();
//        echo response()->json($sessionIds)->getContent();

        $headers = [
            "title" => [
                [
                    [
                        "value" => $survey->name,
                        "span"  => 1
                    ]
                ],
            ],
            "slug"  => [
                [
                    [
                        "value" => $survey->slug,
                        "span"  => 1
                    ]
                ]
            ]
        ];

        $headers["elements"] = [];
        $headers["question"] = [];
        $headers["options"]  = [];

        $cellPosition  = 0;
        $cellPositions = [];

        foreach ($survey->survey_steps as $step) {
            $elementType = ucfirst($step->survey_element_type->key);
            $className   = 'Twoavy\EvaluationTool\SurveyElementTypes\EvaluationToolSurveyElementType' . $elementType;
            if (class_exists($className)) {
                if (method_exists($className, "getExportDataHeaders")) {
                    $exportData = $className::getExportDataHeaders($step, $language);
                    $e          = 0;
                    foreach ($exportData as $key => $row) {
                        $headers[$key][] = $row;
                        if ($e == 0) {
                            // set cell position
                            $cellPositions["step_" . $step->id] = $cellPosition + $row[0]["span"];
                            $cellPosition                       += $row[0]["span"];
                        }
                        $e++;
                    }
                }
            }
        }

        $resultData = [];
        foreach ($sessionIds as $sessionId) {
            $sessionResults = $results->where("session_id", $sessionId);
            foreach ($sessionResults as $result) {
                $elementType = ucfirst($result->survey_step->survey_element_type->key);
                $className   = 'Twoavy\EvaluationTool\SurveyElementTypes\EvaluationToolSurveyElementType' . $elementType;
                if (class_exists($className)) {
                    if (method_exists($className, "getExportDataResult")) {
//                        echo $className . PHP_EOL;
                        if (!isset($resultData[$sessionId])) {
                            $resultData[$sessionId] = [];
                        }
                        $resultData[$sessionId] = array_merge($resultData[$sessionId],
                            $className::getExportDataResult($step->survey_element, $language, $result, $cellPositions["step_" . $result->survey_step_id]));
                    }
                }
            }
        }

//        echo response()->json($resultData)->getContent();

        return [
            "headers" => $headers,
            "results" => $resultData
        ];
    }
}
