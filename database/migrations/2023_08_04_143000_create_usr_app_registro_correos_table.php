<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsrAppRegistroCorreosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('usr_app_registro_correos', function (Blueprint $table) {
            $table->id();
            $table->string('remitente');
            $table->string('destinatario');
            $table->string('con_copia')->nullable();
            $table->string('con_copia_oculta')->nullable();
            $table->string('asunto');
            $table->string('mensaje',1000);
            $table->string('adjunto')->nullable();
            $table->string('modulo')->nullable();
            $table->string('area')->nullable();
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
        Schema::dropIfExists('usr_app_registro_correos');
    }
}
