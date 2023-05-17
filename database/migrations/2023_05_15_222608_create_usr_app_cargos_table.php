<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsrAppCargosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('usr_app_cargos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre',100);
            $table->string('descripcion',150);
            $table->unsignedBigInteger('cliente_id');
            $table->unsignedBigInteger('riesgo_laboral_id');
            $table->foreign('cliente_id')->references('id')->on('usr_app_clientes')->onDelete('cascade')->onUpdate('cascade');
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
        Schema::dropIfExists('usr_app_cargos');
    }
}
