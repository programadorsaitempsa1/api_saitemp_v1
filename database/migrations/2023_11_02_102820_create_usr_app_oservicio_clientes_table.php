<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsrAppOservicioClientesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('usr_app_oservicio_clientes', function (Blueprint $table) {
            $table->id();
            $table->string('nit_ndocumento')->unique();
            $table->string('nombre_razon_social');
            $table->string('nombre_solicitante');
            $table->string('celular_solicitante');
            $table->string('correo_solicitante');
            $table->unsignedBigInteger('usuario_id');
            $table->foreign('usuario_id')->references('id')->on('usr_app_usuarios')->onDelete('cascade')->onUpdate('cascade');
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
        Schema::dropIfExists('usr_app_oservicio_clientes');
    }
}
