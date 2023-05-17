<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsrAppDatosFinancierosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('usr_app_datos_financieros', function (Blueprint $table) {
            $table->id();
            $table->double('ingreso_mensual');
            $table->double('otros_ingresos');
            $table->double('total_ingresos');
            $table->double('costos_gastos_mensual');
            $table->string('detalle_otros_ingresos',100);
            $table->double('reintegro_costos_gastos');
            $table->double('activos');
            $table->double('pasivos');
            $table->double('patrimonio');
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
        Schema::dropIfExists('usr_app_datos_financieros');
    }
}
