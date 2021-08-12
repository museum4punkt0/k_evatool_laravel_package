<?php

namespace Twoavy\EvaluationTool\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EvaluationToolSurvey extends Model
{
    use HasFactory;

    protected $fillable = ["name", "description", "published", "publish_up", "publish_down"];
}
