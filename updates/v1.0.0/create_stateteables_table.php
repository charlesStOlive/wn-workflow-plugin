<?php namespace Waka\Workflow\Updates;

use Winter\Storm\Database\Schema\Blueprint;
use Winter\Storm\Database\Updates\Migration;
use Schema;

class CreateStateteablesTable extends Migration
{
    public function up()
    {
        Schema::create('waka_workflow_state_log', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('name');
            $table->integer('state_logeable_id')->nullable();
            $table->string('state_logeable_type')->nullable();
            $table->string('state')->nullable();
            $table->string('user')->nullable();
            $table->string('wf')->nullable();
            $table->index('state_logeable_type', 'state_logeable_type_idx');
            $table->index('state_logeable_id', 'state_logeable_id_idx');
            $table->index('state', 'state_idx');
            $table->index('created_at', 'created_at_idx');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('waka_workflow_state_log');
    }
}
