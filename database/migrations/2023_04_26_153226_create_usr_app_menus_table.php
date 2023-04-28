<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsrAppMenusTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('usr_app_menus', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 50)->nullable()->default('');
            $table->string('url', 200)->nullable()->default('');
            $table->string('icon', 100)->nullable()->default('');
            $table->boolean('urlExterna')->nullable()->default(0);
            $table->integer('posicion')->nullable();
            $table->boolean('oculto')->nullable()->default(0);
            $table->unsignedBigInteger('categoria_menu_id');
            $table->longText('powerbi')->nullable()->default('');
            $table->foreign('categoria_menu_id')->references('id')->on('usr_app_categorias_menu')->onDelete('cascade')->onUpdate('cascade');
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
        Schema::dropIfExists('usr_app_menus');
    }
}
