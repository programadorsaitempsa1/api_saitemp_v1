<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsrAppRefComTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('usr_app_ref_com', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_cli');
            $table->string('nom_ref',145);
            $table->string('nom_cont',145);
            $table->string('tel',45);
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
        Schema::dropIfExists('usr_app_ref_com');
    }
}
