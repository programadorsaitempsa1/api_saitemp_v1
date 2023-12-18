<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsrAppOrdenServicioCargosExamenTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('usr_app_orden_servicio_cargos_examen', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cargo_id');
            $table->unsignedBigInteger('examen_id');
            $table->foreign('cargo_id')->references('id')->on('usr_app_orden_servicio_cargos')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('examen_id')->references('id')->on('usr_app_lista_examenes')->onDelete('no action')->onUpdate('no action');
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
        Schema::dropIfExists('usr_app_orden_servicio_cargos_examen');
    }
}
