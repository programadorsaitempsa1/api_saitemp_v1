<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEnvioCorreosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('envio_correos', function (Blueprint $table) {
            $table->id();
            $table->string('modulo');
            $table->string('remitente');
            $table->string('destinatario');
            $table->string('con_copia');
            $table->string('con_copia_oculta');
            $table->string('asunto');
            $table->string('mensaje');
            $table->string('adjunto');
            $table->string('campos');
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
        Schema::table('envio_correos', function (Blueprint $table) {
            //
        });
    }
}
