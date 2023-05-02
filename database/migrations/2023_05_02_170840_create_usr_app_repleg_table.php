<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsrAppReplegTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('usr_app_repleg', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_cli');
            $table->string('nom_rep_legal',145);
            $table->integer('id_tip_ide');
            $table->string('num_ide',45);
            $table->string('dir_rep_legal',45);
            $table->string('tel_rep_legal',45);
            $table->integer('id_ciu_exp');
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
        Schema::dropIfExists('usr_app_repleg');
    }
}
