<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsrAppOrigenesFondosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('usr_app_origenes_fondos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tipo_origen_fondos_id');
            $table->string('otro_origen',100);
            $table->unsignedBigInteger('tipo_origen_medios_id');
            $table->unsignedBigInteger('tipo_origen_medios2_id');
            $table->boolean('alto_manejo_efectivo');
            $table->unsignedBigInteger('cliente_id');
            $table->foreign('tipo_origen_fondos_id')->references('id')->on('usr_app_tipos_origen_fondos')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('tipo_origen_medios_id')->references('id')->on('usr_app_tipos_origen_medios')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('cliente_id')->references('id')->on('usr_app_clientes')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('usr_app_origenes_fondos');
    }
}
