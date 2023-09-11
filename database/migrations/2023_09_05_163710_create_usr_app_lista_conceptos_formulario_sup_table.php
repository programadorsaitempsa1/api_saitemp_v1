<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsrAppListaConceptosFormularioSupTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('second_db')->create('usr_app_lista_conceptos_formulario_sup', function (Blueprint $table) {
            $table->id();
            $table->string('nombre_concepto');
            $table->string('descripcion')->nullable();
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
        Schema::connection('second_db')->dropIfExists('usr_app_lista_conceptos_formulario_sup');
    }
}
