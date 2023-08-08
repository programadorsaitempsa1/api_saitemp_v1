<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsrAppAccionistasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('usr_app_accionistas', function (Blueprint $table) {
            $table->id();
            // $table->char('tipo_identificacion_id',2); 
            $table->string('identificacion',20)->nullable();
            $table->string('accionista',100)->nullable();
            $table->string('participacion',10)->nullable();
            $table->unsignedBigInteger('cliente_id');
            // $table->foreign('tipo_identificacion_id')->references('cod_tip')->on('gen_tipide')->onDelete('cascade')->onUpdate('cascade');
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
        Schema::dropIfExists('usr_app_accionistas');
    }
}
