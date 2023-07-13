<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifyUniqueConstraintInUsrAppClientes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
         // Eliminar la restricción única existente
         Schema::table('usr_app_clientes', function (Blueprint $table) {
            $table->dropUnique('usr_app_clientes_nit_unique');
            $table->dropUnique('usr_app_clientes_numero_identificacion_unique');
        });

        // Agregar una nueva restricción única con la configuración deseada
        // Schema::table('usr_app_clientes', function (Blueprint $table) {
        //     $table->unique('nit', 'usr_app_clientes_nit_unique');
        //     $table->unique('numero_identificacion', 'usr_app_clientes_numero_identificacion_unique');
        // });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('usr_app_clientes', function (Blueprint $table) {
            $table->dropUnique('usr_app_clientes_nit_unique');
            $table->unique('nit');
            $table->dropUnique('usr_app_clientes_numero_identificacion_unique');
            $table->unique('numero_identificacion');
        });
    }
}
