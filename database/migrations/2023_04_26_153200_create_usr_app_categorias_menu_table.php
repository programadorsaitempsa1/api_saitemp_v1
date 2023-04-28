<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsrAppCategoriasMenuTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('usr_app_categorias_menu', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 50)->nullable()->default('');
            $table->string('icon', 100)->nullable()->default('');
            $table->integer('posicion')->nullable();
            $table->boolean('oculto')->nullable()->default(0);
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
        Schema::dropIfExists('usr_app_categorias_menu');
    }
}
