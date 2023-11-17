<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsrAppFormularioSupervisionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('second_db')->create('usr_app_formulario_supervision', function (Blueprint $table) {
            $table->id();
            $table->string('fecha_hora');
            $table->string('supervisor_id');
            $table->string('persona_contactada');
            // $table->char('cliente_id',15); este campo se agregó desde sql
            $table->string('direccion');
            $table->string('municipio');
            $table->longText('descripcion');
            $table->string('firma_supervisor');
            $table->string('firma_persona_contactada');
            $table->string('latitud');
            $table->string('longitud');
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
        Schema::connection('second_db')->dropIfExists('usr_app_formulario_supervision');
    }
}
