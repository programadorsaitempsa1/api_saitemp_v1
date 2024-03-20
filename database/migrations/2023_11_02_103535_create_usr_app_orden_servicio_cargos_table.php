<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsrAppOrdenServicioCargosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('usr_app_orden_servicio_cargos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cargo_id');
            $table->unsignedBigInteger('riesgo_laboral_id');
            $table->unsignedBigInteger('servicio_id');
            $table->longText('funcion_cargo')->nullable();
            $table->foreign('servicio_id')->references('id')->on('usr_app_orden_servicio_servicio_solicitado')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('cargo_id')->references('id')->on('usr_app_lista_cargos')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('riesgo_laboral_id')->references('id')->on('usr_app_riesgos_laborales')->onDelete('NO ACTION')->onUpdate('NO ACTION');
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
        Schema::dropIfExists('usr_app_orden_servicio_cargos');
    }
}
