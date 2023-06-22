<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsrAppPeriodicidadLiquidacionNominaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('usr_app_periodicidad_liquidacion_nominas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre',50);
            $table->string('descripcion',150);
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
        Schema::dropIfExists('usr_app_periodicidad_liquidacion_nominas');
    }
}
