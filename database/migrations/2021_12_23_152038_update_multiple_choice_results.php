<?php

use Illuminate\Database\Migrations\Migration;
use Twoavy\EvaluationTool\Models\EvaluationToolSurveyElement;
use Twoavy\EvaluationTool\Models\EvaluationToolSurveyStep;
use Twoavy\EvaluationTool\Models\EvaluationToolSurveyStepResult;

class UpdateMultipleChoiceResults extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $multipleChoiceElements = EvaluationToolSurveyElement::where("survey_element_type_id", 2)->get();
        $multipleChoiceSteps    = EvaluationToolSurveyStep::whereIn("survey_element_id", $multipleChoiceElements->pluck("id"))->get();
        EvaluationToolSurveyStepResult::whereIn("survey_step_id", $multipleChoiceSteps->pluck("id"))
            ->get()
            ->each(function ($result) {
                if (isset($result->result_value["selected"])) {
                    $selectedPrepared = [
                        "selected" => []
                    ];
                    $executeSave      = false;
                    foreach ($result->result_value["selected"] as $selected) {
                        if (gettype($selected) != "array") {
                            // only execute if at least one element is not an array
                            $executeSave                    = true;
                            $selectedPrepared["selected"][] = ["value" => $selected];
                        }
                    }
                    if ($executeSave) {
                        $result->result_value = $selectedPrepared;
                        $result->saveQuietly();
                    }
                }
            });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $multipleChoiceElements = EvaluationToolSurveyElement::where("survey_element_type_id", 2)->get();
        $multipleChoiceSteps    = EvaluationToolSurveyStep::whereIn("survey_element_id", $multipleChoiceElements->pluck("id"))->get();
        EvaluationToolSurveyStepResult::whereIn("survey_step_id", $multipleChoiceSteps->pluck("id"))
            ->get()
            ->each(function ($result) {
                if (isset($result->result_value["selected"])) {
                    $selectedPrepared = [
                        "selected" => []
                    ];
                    $executeSave      = false;
                    foreach ($result->result_value["selected"] as $selected) {
                        if (gettype($selected) == "array") {
                            // only execute if at least one element is an array
                            $executeSave                    = true;
                            $selectedPrepared["selected"][] = $selected["value"];
                        }
                    }
                    if ($executeSave) {
                        $result->result_value = $selectedPrepared;
                        $result->saveQuietly();
                    }
                }
            });
    }
}
