<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsrAppDepartamentosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('usr_app_departamentos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('codigo_dane');
            $table->string('indicativo_tel');
            $table->unsignedBigInteger('pais_id');
            $table->foreign('pais_id')->references('id')->on('usr_app_paises')->onDelete('cascade')->onUpdate('cascade');
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
        Schema::dropIfExists('usr_app_departamentos');
    }
}
