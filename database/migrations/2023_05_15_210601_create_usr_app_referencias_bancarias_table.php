<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsrAppReferenciasBancariasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('usr_app_referencias_bancarias', function (Blueprint $table) {
            $table->id();
            // $table->char('banco_id',2);
            $table->string('numero_cuenta',20);
            $table->unsignedBigInteger('tipo_cuenta_id');
            $table->string('sucursal',100);
            $table->string('telefono',20);
            $table->string('contacto',100);
            $table->unsignedBigInteger('cliente_id');
            // $table->foreign('banco_id')->references('cod_ban')->on('gen_bancos')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('tipo_cuenta_id')->references('id')->on('usr_app_tipos_cuenta_banco')->onDelete('cascade')->onUpdate('cascade');
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
        Schema::dropIfExists('usr_app_referencias_bancarias');
    }
}
