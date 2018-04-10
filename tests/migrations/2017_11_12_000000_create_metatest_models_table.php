<?php

use Illuminate\Database\Migrations\Migration;

class CreateMetatestModelsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('metatest_model1s', function ($table) {
            $table->increments('id');
            $table->string('title', 255);
            $table->text('body');
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
        Schema::drop('metatest_model1s');
    }
}