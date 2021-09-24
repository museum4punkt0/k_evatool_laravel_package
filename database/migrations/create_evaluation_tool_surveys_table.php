<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEvaluationToolSurveysTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('evaluation_tool_surveys', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 200);
            $table->string('name', 100);
            $table->string('description', 500)->nullable();
            $table->boolean('published')->default(true);
            $table->timestamp('publish_up')->nullable();
            $table->timestamp('publish_down')->nullable();
            $table->json('admin_layout')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table('evaluation_tool_surveys', function (Blueprint $table) {
            $table->unsignedBigInteger('created_by')->nullable()->after('created_at');
            $table->unsignedBigInteger('updated_by')->nullable()->after('updated_at');
            $table->unsignedBigInteger('deleted_by')->nullable()->after('deleted_at');
        });

        Schema::table('evaluation_tool_surveys', function (Blueprint $table) {
            $table->unsignedBigInteger('created_by')->nullable()->after('created_at');
            $table->unsignedBigInteger('updated_by')->nullable()->after('updated_at');
            $table->unsignedBigInteger('deleted_by')->nullable()->after('deleted_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('evaluation_tool_surveys');
    }
}
