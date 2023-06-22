<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsrAppDocumentosClienteTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('usr_app_documentos_cliente', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tipo_documento_id');
            $table->string('ruta',100);
            $table->string('abreviacion',5)->nullable();
            $table->string('tipo_archivo',10)->nullable();
            $table->string('descripcion',150)->nullable();
            $table->unsignedBigInteger('cliente_id');
            $table->foreign('tipo_documento_id')->references('id')->on('usr_app_tipos_documento')->onDelete('cascade')->onUpdate('cascade');
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
        Schema::dropIfExists('usr_app_documentos_cliente');
    }
}
