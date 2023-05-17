<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsrAppCalidadTributariaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('usr_app_calidad_tributaria', function (Blueprint $table) {
            $table->id();
            $table->boolean('gran_contribuyente');
            $table->string('resolucion_gran_contribuyente',50);
            $table->date('fecha_gran_contribuyente');
            $table->boolean('auto_retenedor');
            $table->string('resolucion_auto_retenedor',50);
            $table->date('fecha_auto_retenedor');
            $table->boolean('exento_impuesto_rent');
            $table->string('resolucion_exento_impuesto_rent',50);
            $table->date('fecha_exento_impuesto_rent');
            $table->unsignedBigInteger('cliente_id');
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
        Schema::dropIfExists('usr_app_calidad_tributaria');
    }
}
