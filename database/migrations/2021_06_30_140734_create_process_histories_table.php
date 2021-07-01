<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProcessHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('process_histories', function (Blueprint $table) {
            $table->id();
            $table->string('uid');
            $table->dateTime('process_start');
            $table->dateTime('process_end')->nullable();
            $table->integer('entity_id');
            $table->integer('lines_count');
            $table->integer('lines_success')->default(0);
            $table->integer('lines_error')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('process_histories');
    }
}
