<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsrAppRepresentantesLegalesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('usr_app_representantes_legales', function (Blueprint $table) {
            $table->id();
            $table->string('nombre',100)->nullable();
            // $table->char('tipo_identificacion_id',2);
            $table->string('identificacion',20)->nullable();
            $table->unsignedBigInteger('municipio_expedicion_id');
            $table->string('correo_electronico',100)->nullable();
            $table->string('telefono',20)->nullable();
            $table->unsignedBigInteger('cliente_id');
            // $table->foreign('tipo_identificacion_id')->references('cod_tip')->on('gen_tipide')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('municipio_expedicion_id')->references('id')->on('usr_app_municipios')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('cliente_id')->references('id')->on('usr_app_clientes')->onDelete('cascade')->onUpdate('cascade');
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
        Schema::dropIfExists('usr_app_representantes_legales');
    }
}
