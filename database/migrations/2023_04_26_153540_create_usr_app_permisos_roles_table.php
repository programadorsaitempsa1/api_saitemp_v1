<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsrAppPermisosRolesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('usr_app_permisos_roles', function (Blueprint $table) {
            $table->id();
            $table->UnsignedBigInteger('rol_id');
            $table->UnsignedBigInteger('permiso_id');
            $table->string('descripcion', 300)->nullable();
            $table->foreign('rol_id')->references('id')->on('usr_app_roles')->onDelete('cascade');
            $table->foreign('permiso_id')->references('id')->on('usr_app_permisos')->onDelete('cascade');
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
        Schema::dropIfExists('usr_app_permisos_roles');
    }
}
