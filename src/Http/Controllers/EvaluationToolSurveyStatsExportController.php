<?php

namespace Twoavy\EvaluationTool\Http\Controllers;

use App\Http\Controllers\Controller;
use Carbon\CarbonInterval;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Csv;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use StdClass;
use Twoavy\EvaluationTool\Helpers\EvaluationToolHelper;
use Twoavy\EvaluationTool\Http\Requests\EvaluationToolSurveyStatsDownloadRequest;
use Twoavy\EvaluationTool\Http\Requests\EvaluationToolSurveyStatsExportRequest;
use Twoavy\EvaluationTool\Models\EvaluationToolSurvey;
use Twoavy\EvaluationTool\Models\EvaluationToolSurveyLanguage;
use Twoavy\EvaluationTool\Models\EvaluationToolSurveyStep;
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

    /**
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public function getStatsExport(EvaluationToolSurvey $survey, EvaluationToolSurveyStatsExportRequest $request): JsonResponse
    {
        $filename = "eva_tool_export_survey_" . $survey->id;

        $results = EvaluationToolSurveyStepResult::whereIn("survey_step_id",
            $survey->survey_steps
                ->pluck("id"))
//            ->orderByRaw(DB::raw("FIELD(survey_step_id, " . implode(",", $ordering->toArray()) . ") ASC"))
            ->orderBy("answered_at", "DESC");

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

        $results = $results->get();

        $execute = false;
        if ($request->has("execute")) {
            $execute = true;

            $i = 1;

            // excel export
            if ($request->exportType == "xlsx") {
                $filename .= ".xlsx";

                $spreadsheet = new Spreadsheet();

                // global style
                $spreadsheet->getDefaultStyle()->getFont()->setName('Arial');
                $spreadsheet->getDefaultStyle()->getFont()->setSize(10);

                $sheet = $spreadsheet->getActiveSheet();

                $preparedResults = $this->prepareResultsForExcel($results, $survey);

                $r = 1;
                foreach ($preparedResults["headers"] as $resultRow) {
                    $c = 1;
                    foreach ($resultRow as $cellItem) {
                        foreach ($cellItem as $cellSubItem) {
                            $valueToWrite = html_entity_decode(strip_tags($cellSubItem["value"]));
                            $sheet->setCellValueByColumnAndRow($c, $r, $valueToWrite);
                            if (isset($cellSubItem["span"]) && $cellSubItem["span"] > 1) {
                                $sheet->mergeCellsByColumnAndRow($c, $r, ($c + $cellSubItem["span"] - 1), $r);
                                $c = $c + $cellSubItem["span"];
                            } else {
                                $c++;
                            }
                        }
                    }
                    $r++;
                }

                // set properties
                $spreadsheet->getProperties()
                    ->setCreator("EVA-Tool")
                    ->setLastModifiedBy("EVA-Tool")
                    ->setTitle("EVA-Tool")
                    ->setSubject("Auswertungsansicht")
//                    ->setDescription("")
                    ->setKeywords("evaluation tool online")
                    ->setCategory("EVA-Tool Result Excel File");

                // set colors
                $spreadsheet
                    ->getActiveSheet()
                    ->getStyle('A1:' . $sheet->getHighestDataColumn() . "4")
                    ->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()
                    ->setRGB('333333');

                $spreadsheet
                    ->getActiveSheet()
                    ->getStyle('A1:' . $sheet->getHighestDataColumn() . "4")
                    ->getFont()
                    ->getColor()
                    ->setRGB('FFFFFF');

                foreach ($preparedResults["results"] as $resultRow) {
                    foreach ($resultRow as $resultCell) {
                        $valueToWrite = html_entity_decode(strip_tags($resultCell["value"]));
                        if (isset($resultCell["format"])) {
                            $spreadsheet->getActiveSheet()
                                ->getCellByColumnAndRow($resultCell["position"], $r)
                                ->setValueExplicit(
                                    $valueToWrite,
                                    $resultCell["format"]
                                );
                        } else {
                            $sheet->setCellValueByColumnAndRow($resultCell["position"], $r, $valueToWrite);
                        }
                    }
                    $r++;
                }

                // header style
                $styleArray = [
                    'font' => [
                        'bold' => true,
                        'size' => 16
                    ],
                ];
                $spreadsheet->getActiveSheet()->getStyle('A1')->applyFromArray($styleArray);


                // set widths
                foreach (range('A', $sheet->getHighestDataColumn()) as $col) {
                    $sheet->getColumnDimension($col)
                        ->setAutoSize(true);
//                        ->setWidth(100, 'pt');
                }

                $sheet->freezePane("E5");

                // set alignment
                $spreadsheet->getActiveSheet()->getStyle('A5:' . $sheet->getHighestDataColumn() . $sheet->getHighestDataRow())
                    ->getAlignment()->setVertical(Alignment::VERTICAL_TOP);

                // set text wrap
                $spreadsheet->getActiveSheet()->getStyle('A5:' . $sheet->getHighestDataColumn() . $sheet->getHighestDataRow())
                    ->getAlignment()->setWrapText(true);

                $writer = new Xlsx($spreadsheet);
                $writer->save($this->disk->path($filename));
            }

            // csv export
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

            // json export
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

    /**
     * Prepares the headers rows for the export to Excel
     * @param $results
     * @param EvaluationToolSurvey $survey
     * @return array
     * @throws Exception
     */
    public function prepareResultsForExcel($results, EvaluationToolSurvey $survey): array
    {

        $language = EvaluationToolSurveyLanguage::all()->first();

        $sessionIds = $results->map(function ($result) {
            return $result->only("session_id");
        })->groupBy("session_id")->keys();

        $headers = [
            "title" => [
                [
                    [
                        "value" => $survey->name,
                        "span"  => $survey->survey_steps->count()
                    ]
                ],
            ],
            /*"slug"  => [
                [
                    [
                        "value" => $survey->slug,
                        "span"  => $survey->survey_steps->count()
                    ]
                ]
            ]*/
        ];

        $headers["elements"]   = [];
        $headers["elements"][] = [
            [
                "value" => "",
                "span"  => 4
            ]
        ];

        $headers["question"]   = [];
        $headers["question"][] = [
            [
                "value" => "",
                "span"  => 4
            ]
        ];

        $headers["options"]   = [];
        $headers["options"][] = [
            [
                "value" => "Session",
                "span"  => 1
            ],
            [
                "value" => "Start",
                "span"  => 1
            ],
            [
                "value" => "Ende",
                "span"  => 1
            ],
            [
                "value" => "Dauer",
                "span"  => 1
            ]
        ];


        $cellPosition  = 0;
        $cellPositions = [];

        // order steps
        $ordering = EvaluationToolHelper::sortSurveySteps($survey);
        $surveySteps = EvaluationToolSurveyStep::whereIn("id", $ordering->toArray())
            ->orderByRaw(DB::raw("FIELD(id, " . implode(",", $ordering->toArray()) . ") ASC"))
            ->get();

        foreach ($surveySteps as $step) {
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

        // shift all position to left
        $cellPositionsPrepared = [];
        $keys                  = array_keys($cellPositions);
        $c                     = 0;
        $cellOffset            = 4;
        foreach ($cellPositions as $key => $cellPosition) {
            if ($c == 0) {
                $cellPositionsPrepared[$key] = 1 + $cellOffset;
            } else {
                $cellPositionsPrepared[$key] = 1 + $cellPositions[$keys[$c - 1]] + $cellOffset;
            }
            $c++;
        }

        $resultData = [];
        foreach ($sessionIds as $sessionId) {
            $sessionResults        = $results->where("session_id", $sessionId);
            $orderedSessionResults = $sessionResults->sortBy(function ($result) {
                return $result->answered_at;
            })->values();

            // set german date format
            Carbon::setLocale('de');

            $firstResult = $orderedSessionResults->first()->answered_at;
            $lastResult  = $orderedSessionResults->last()->answered_at;
            $duration    = $firstResult->diffInSeconds($lastResult);


            foreach ($sessionResults as $result) {
                $elementType = ucfirst($result->survey_step->survey_element_type->key);
                $className   = 'Twoavy\EvaluationTool\SurveyElementTypes\EvaluationToolSurveyElementType' . $elementType;
                if (class_exists($className)) {
                    if (method_exists($className, "getExportDataResult")) {
                        if (!isset($resultData[$sessionId])) {
                            $resultData[$sessionId]   = [];
                            $resultData[$sessionId][] = ["value" => substr($sessionId, 0, 8), "position" => 1, "format" => DataType::TYPE_STRING2];
                            $resultData[$sessionId][] = ["value" => $firstResult->format("d.m.Y H:i:s"), "position" => 2];
                            $resultData[$sessionId][] = ["value" => $lastResult->format("d.m.Y H:i:s"), "position" => 3];
                            $resultData[$sessionId][] = ["value" => CarbonInterval::seconds($duration)->cascade()->forHumans(), "position" => 4];
                        }
                        $resultData[$sessionId] = array_merge($resultData[$sessionId],
                            $className::getExportDataResult($step->survey_element, $language, $result, $cellPositionsPrepared["step_" . $result->survey_step_id]));
                    }
                }
            }
        }

        return [
            "headers" => $headers,
            "results" => $resultData
        ];
    }
}
