<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsrAppTiposDocumentoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('usr_app_tipos_documento', function (Blueprint $table) {
            $table->id();
            $table->string('nombre',300);
            $table->string('abreviacion',3)->nullable();
            $table->string('tipo_archivo',10)->nullable();
            $table->unsignedBigInteger('tipo_proveedor_id');
            $table->foreign('tipo_proveedor_id')->references('id')->on('usr_app_tipo_proveedor')->onDelete('NO ACTION')->onUpdate('NO ACTION');
            $table->timestamps('');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('usr_app_tipos_documento');
    }
}
