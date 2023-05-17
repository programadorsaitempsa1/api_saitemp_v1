<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsrAppClientesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('usr_app_clientes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('operacion_id');
            $table->boolean('contratacion_directa');
            $table->boolean('atraccion_seleccion');
            $table->unsignedBigInteger('tipo_persona_id');
            // $table->char('tipo_identificacion_id',2);
            $table->string('numero_identificacion',50);
            $table->date('fecha_exp_documento');
            $table->string('nit',50);
            $table->string('digito_verificacion',4);
            $table->string('razon_social',200);
            $table->unsignedBigInteger('riesgo_cliente_id');
            $table->date('fecha_constitucion');
            $table->unsignedBigInteger('actividad_ciiu_id');
            $table->unsignedBigInteger('estrato_id');
            $table->string('direccion_empresa',100);
            $table->char('municipio_id',5);
            $table->string('contacto_empresa',150);
            $table->string('correo_empresa',200);
            $table->string('telefono_empresa',20);
            $table->string('celular_empresa',20);
            $table->string('numero_matricula',50);
            $table->char('municipio_matricula_id',5);
            $table->unsignedBigInteger('actividad_economica_id');
            $table->unsignedBigInteger('sociedad_comercial_id');
            $table->string('otra',150);
            $table->string('aiu_negociado',50);
            $table->string('plazo_pago',50);
            // $table->char('vendedor_id');
            $table->string('acuerdo_comercial',200);
            $table->integer('numero_empleados');
            $table->unsignedBigInteger('jornada_laboral_id');
            $table->unsignedBigInteger('rotacion_personal_id');
            $table->boolean('junta_directiva');
            $table->boolean('responsable_inpuesto_ventas');
            $table->string('correo_facturacion_electronica',200);
            // $table->char('sucursal_facturacion_id',3);
            $table->boolean('declaraciones_autirizaciones');
            $table->boolean('transparencia_etica');
            $table->boolean('tratamiento_datos_personales');
            $table->foreign('operacion_id')->references('id')->on('usr_app_operaciones')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('tipo_persona_id')->references('id')->on('usr_app_tipos_persona')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('riesgo_cliente_id')->references('id')->on('usr_app_riesgos_laborales')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('actividad_ciiu_id')->references('id')->on('usr_app_actividades_ciiu')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('estrato_id')->references('id')->on('usr_app_estratos')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('actividad_economica_id')->references('id')->on('usr_app_actividades_economicas')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('sociedad_comercial_id')->references('id')->on('usr_app_sociedades_comerciales')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('jornada_laboral_id')->references('id')->on('usr_app_jornadas_laborales')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('rotacion_personal_id')->references('id')->on('usr_app_rotaciones_personal')->onDelete('cascade')->onUpdate('cascade');
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
        Schema::dropIfExists('usr_app_clientes');
    }
}
