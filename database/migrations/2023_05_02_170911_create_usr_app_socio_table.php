<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsrAppSocioTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('usr_app_socio', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_cli');
            $table->string('nom_socio',145);
            $table->integer('id_tip_ide');
            $table->string('num_ide',145);
            $table->string('porcentaje',45);
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
        Schema::dropIfExists('usr_app_socio');
    }
}
