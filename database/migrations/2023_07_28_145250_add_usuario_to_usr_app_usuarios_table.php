<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUsuarioToUsrAppUsuariosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('usr_app_usuarios', function (Blueprint $table) {
            $table->string("usuario")->nullable();
            $table->string("contrasena_correo")->nullable();
            $table->string("imagen_firma_1")->nullable();
            $table->string("imagen_firma_2")->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('usr_app_usuarios', function (Blueprint $table) {
            $table->dropColumn('usuario');
            $table->dropColumn('contrasena_correo');
            $table->dropColumn('imagen_firma_1');
            $table->dropColumn('imagen_firma_2');
        });
    }
}
