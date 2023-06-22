<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsrAppActividadesCiiuTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('usr_app_actividades_ciiu', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('codigo_ciiu_id');
            $table->string('codigo_actividad',10);
            $table->string('descripcion',2000)->nullable();
            $table->foreign('codigo_ciiu_id')->references('id')->on('usr_app_codigos_ciiu')->onDelete('cascade')->onUpdate('cascade');
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
        Schema::dropIfExists('usr_app_actividades_ciiu');
    }
}
