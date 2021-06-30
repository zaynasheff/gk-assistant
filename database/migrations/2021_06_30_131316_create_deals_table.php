<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDealsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('deals', function (Blueprint $table) {
            $table->id();
            $table->integer('entity_id')->comment('Тип сущности');
            $table->string('field_code')->comment('Код поля');
            $table->string('field_type')->comment('Тип поля');
            $table->tinyInteger('required')->comment('Обязательное или нет');
            $table->string('title')->comment('Человекочитаемое название поля');
            $table->tinyInteger('forbidden_to_edit')->comment('Запрещенное к редактированию или нет');
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
        Schema::dropIfExists('deals');
    }
}
