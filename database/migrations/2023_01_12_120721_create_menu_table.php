<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMenuTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('menus', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 50)->nullable()->default('');
            $table->string('url', 200)->nullable()->default('');
            $table->string('icon', 100)->nullable()->default('');
            $table->boolean('urlExterna')->nullable()->default(0);
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
        Schema::dropIfExists('menus');
    }
}
