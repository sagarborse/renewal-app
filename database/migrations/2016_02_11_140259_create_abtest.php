<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreateAbtest extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('abtest', function(Blueprint $table) {
            $table->increments('id');
            $table->string('domain');
            $table->string('path');
            $table->string('testUrl');
            $table->string('status')->default('inActive');
            $table->integer('visitorCount')->default(0);
            $table->integer('shownCount')->default(0);
            $table->integer('targetPercent')->default(0);
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
        Schema::drop('abtest');
    }
}
