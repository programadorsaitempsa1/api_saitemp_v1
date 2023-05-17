<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsrAppPersonasExpuestasPoliticaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('usr_app_personas_expuestas_politica', function (Blueprint $table) {
            $table->id();
            $table->string('nombre',100);
            // $table->char('tipo_identificacion_id',2);
            $table->string('numero_identificacion',20);
            $table->string('parentesco',50);
            $table->unsignedBigInteger('cliente_id');
            // $table->foreign('tipo_identificacion_id')->references('id')->on('cod_tip')->onDelete('gen_tipide')->onUpdate('cascade');
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
        Schema::dropIfExists('usr_app_personas_expuestas_politica');
    }
}
