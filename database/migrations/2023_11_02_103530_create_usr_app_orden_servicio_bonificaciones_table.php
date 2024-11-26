<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsrAppOrdenServicioBonificacionesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('usr_app_orden_servicio_bonificaciones', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('bonificacion_id');
            $table->string('valor_bonificacion');
            $table->unsignedBigInteger('servicio_id');
            $table->foreign('bonificacion_id')->references('id')->on('usr_app_bonificaciones_orden_servicio')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('servicio_id')->references('id')->on('usr_app_orden_servicio_servicio_solicitado')->onDelete('cascade')->onUpdate('cascade');
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
        Schema::dropIfExists('usr_app_orden_servicio_bonificaciones');
    }
}
