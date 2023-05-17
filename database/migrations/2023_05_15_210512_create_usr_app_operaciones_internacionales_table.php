<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsrAppOperacionesInternacionalesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('usr_app_operaciones_internacionales', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tipo_operaciones_id');
            $table->unsignedBigInteger('cliente_id');
            $table->foreign('tipo_operaciones_id')->references('id')->on('usr_app_tipo_operaciones_internacionales')->onDelete('cascade')->onUpdate('cascade');
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
        Schema::dropIfExists('usr_app_operaciones_internacionales');
    }
}
