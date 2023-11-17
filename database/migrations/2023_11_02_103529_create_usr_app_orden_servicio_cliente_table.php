<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsrAppOrdenServicioClienteTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('usr_app_orden_servicio_clientes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tipo_persona');
            // $table->unsignedBigInteger('tipo_identificacion');
            $table->string('numero_identificacion')->nullable();
            $table->string('nit')->nullable();
            $table->string('vacantes_disponibles')->nullable();
            $table->string('nombre_razon_social');
            $table->string('nombre_solicitante');
            $table->string('cargo_solicitante');
            $table->string('celular_solicitante');
            $table->string('correo_solicitante');
            $table->unsignedBigInteger('servicio_solicitado');
            $table->unsignedBigInteger('municipio_solicitud');
            $table->foreign('tipo_persona')->references('id')->on('usr_app_tipos_persona')->onDelete('CASCADE')->onUpdate('CASCADE');
            $table->foreign('servicio_solicitado')->references('id')->on('usr_app_servicios_orden_servicio')->onDelete('CASCADE')->onUpdate('CASCADE');
            $table->foreign('municipio_solicitud')->references('id')->on('usr_app_municipios')->onDelete('NO ACTION')->onUpdate('NO ACTION');
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
        Schema::dropIfExists('usr_app_orden_servicio_clientes');
    }
}
