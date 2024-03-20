<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsrAppImagenesFormularioSupervisionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('second_db')->create('usr_app_imagenes_formulario_supervision', function (Blueprint $table) {
            $table->id();
            $table->string('imagen_observacion');
            $table->longText('observacion')->nullable();
            $table->unsignedBigInteger('formulario_id');
            $table->foreign('formulario_id')->references('id')->on('usr_app_formulario_supervision')->onDelete('cascade')->onUpdate('cascade');
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
        Schema::connection('second_db')->dropIfExists('usr_app_imagenes_formulario_supervision');
    }
}
