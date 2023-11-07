<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsrAppOservicioCargosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('usr_app_oservicio_cargos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cliente_id');
            $table->string('nombre');
            $table->string('cantidad_vacantes');
            $table->string('salario');
            $table->date('fecha_inicio');
            $table->dateTime('fecha_solicitud');
            $table->longText('observaciones');
            $table->unsignedBigInteger('ciudad_id');
            $table->foreign('cliente_id')->references('id')->on('usr_app_oservicio_clientes')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('ciudad_id')->references('id')->on('usr_app_municipios')->onUpdate('cascade')->onDelete('cascade');
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
        Schema::dropIfExists('usr_app_oservicio_cargos');
    }
}
