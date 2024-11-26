<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsrAppElementosDePpFormularioSupTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('second_db')->create('usr_app_elementos_de_pp_formulario_sub', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('concepto_id');
            $table->unsignedBigInteger('estado_concepto_id');
            $table->longText('observacion')->nullable();
            $table->unsignedBigInteger('formulario_id');
            $table->foreign('formulario_id')->references('id')->on('usr_app_formulario_supervision')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('concepto_id')->references('id')->on('usr_app_lista_conceptos_formulario_sup')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('estado_concepto_id')->references('id')->on('usr_app_estados_concepto_formulario_sup')->onDelete('cascade')->onUpdate('cascade');
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
        Schema::connection('second_db')->dropIfExists('usr_app_elementos_de_pp_formulario_sub');
    }
}
