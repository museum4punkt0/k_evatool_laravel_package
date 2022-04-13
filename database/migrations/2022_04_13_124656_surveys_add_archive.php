<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class SurveysAddArchive extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('evaluation_tool_surveys', function (Blueprint $table) {
            $table->boolean('archived')->default(false)->after('deleted_by');
            $table->timestamp('archived_at')->nullable()->after('deleted_by');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('evaluation_tool_surveys', function (Blueprint $table) {
            $table->dropColumn('archived');
            $table->dropColumn('archived_at');
        });
    }
}
