<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsrAppOrdenServicioServicioSolicitadoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('usr_app_orden_servicio_servicio_solicitado', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cliente_id');
            $table->string('nombres_candidato');
            $table->string('apellidos_candidato');
            $table->string('celular_candidato');
            $table->string('correo_candidato');
            $table->unsignedBigInteger('municipio_nacimiento');
            // $table->unsignedBigInteger('tipo_identificacion');
            $table->string('numero_identificacion');
            $table->string('salario');
            $table->string('auxilio_transporte')->nullable();
            $table->date('fecha_inicio_labores');
            $table->longText('especificaciones_vinculacion')->nullable();
            $table->longText('especificaciones_seleccion_personal')->nullable();
            $table->unsignedBigInteger('estado_solicitud');
            $table->longText('motivo_cancelacion')->nullable();
            $table->unsignedBigInteger('nombre_laboratorio');
            $table->string('direccion_laboratorio');
            $table->unsignedBigInteger('municipio_ubicacion_laborario');
            $table->string('fecha_hora_examen_medico');
            $table->longText('recomendaciones_examen_medico')->nullable();
            $table->longText('orientacion_ubicacion_laboratorio')->nullable();
            $table->foreign('cliente_id')->references('id')->on('usr_app_orden_servicio_clientes')->onDelete('CASCADE')->onUpdate('CASCADE');
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
        Schema::dropIfExists('usr_app_orden_servicio_servicio_solicitado');
    }
}
