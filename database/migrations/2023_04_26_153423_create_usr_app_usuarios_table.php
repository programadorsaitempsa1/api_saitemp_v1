<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsrAppUsuariosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('usr_app_usuarios', function (Blueprint $table) {
            $table->id();
            $table->string('nombres', 100)->nullable();
            $table->string('apellidos', 100)->nullable();
            $table->string('documento_identidad', 20)->nullable();
            $table->string('email', 100)->unique()->nullable();
            $table->string('password')->nullable();
            $table->unsignedBigInteger('estado_id')->default(1);
            $table->unsignedBigInteger('rol_id')->default(3);
            $table->boolean('oculto')->nullable()->default(0);
            $table->foreign('estado_id')->references('id')->on('usr_app_estados_usuario')->onDelete('cascade');
            $table->foreign('rol_id')->references('id')->on('usr_app_roles')->onDelete('cascade');
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
        Schema::dropIfExists('usr_app_usuarios');
    }
}
