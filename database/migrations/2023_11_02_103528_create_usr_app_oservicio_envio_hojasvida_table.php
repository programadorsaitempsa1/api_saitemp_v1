<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsrAppOservicioEnvioHojasvidaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('usr_app_oservicio_envio_hojasvida', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cliente_id');
            $table->string('nombre_cargo');
            $table->string('fecha_hora_envio');
            $table->string('ruta_documento');
            $table->longText('datos_solicitante');
            $table->foreign('cliente_id')->references('id')->on('usr_app_oservicio_clientes')->onUpdate('cascade')->onDelete('cascade');
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
        Schema::dropIfExists('usr_app_oservicio_envio_hojasvida');
    }
}
