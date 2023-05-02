<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsrAppClienteTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('usr_app_cliente', function (Blueprint $table) {
            $table->id();
            $table->string('opera',45);
            $table->string('tip_per',40);
            $table->integer('id_tip_ide')->nullable();
            $table->bigInteger('num_ide')->default(0);
            $table->date('fec_exp');
            $table->string('razon',150);
            $table->bigInteger('num_ide_RZ')->default(0);
            $table->integer('dv')->nullable();
            $table->string('detalle_act',150);
            $table->string('cod_ciiu',45);
            $table->date('fec_cre');
            $table->string('dir_emp',45);
            $table->integer('estrato')->nullable();
            $table->string('cod_ciu',45);
            $table->string('tel_emp',45);
            $table->string('contacto_emp',150);
            $table->string('email_emp',145);
            $table->string('tel_emp2',45);
            $table->string('act_eco',45);
            $table->string('otra_act_eco',145);
            $table->string('matricula',45);
            $table->string('ciu_matri',45);
            $table->string('soc_comer',45);
            $table->string('otra_soc_comer',145);
            $table->string('RIAV',45);
            $table->string('GCT',45);
            $table->string('AUTR',45);
            $table->string('EIR',45);
            $table->string('num_rel_GCT',145);
            $table->string('num_rel_AUTR',45);
            $table->string('num_rel_EIR',45);
            $table->string('fec_rel_GCT',19);
            $table->string('fec_rel_AUTR',19);
            $table->string('fec_rel_EIR',19);
            $table->string('Email_fac_elec',145);
            $table->string('AIU',45);
            $table->string('plazo',45);
            $table->string('nom_cnt',145);
            $table->integer('id_tip_ide_cnt')->nullable();
            $table->string('num_ide_cnt',45);
            $table->string('num_tel_cnt',45);
            $table->string('ingre_mensual',145);
            $table->string('costo_mes',145);
            $table->string('activos',145);
            $table->string('otr_ing',145);
            $table->string('dt_otro_ing',145);
            $table->string('pasivos',145);
            $table->string('total_ing',145);
            $table->string('reint_costo',145);
            $table->string('patri',145);
            $table->string('ope_inter',45);
            $table->string('tip_op_ext',145);
            $table->string('declaro',45);
            $table->string('ori_fds',145);
            $table->string('otro_ori_fds',145);
            $table->string('rec_fds_1',145);
            $table->string('rec_fds_2',145);
            $table->string('efectivo',45);
            $table->integer('att')->nullable();
            $table->integer('cd')->nullable();
            $table->string('ciu_fact',45);
            $table->string('obs',500);
            $table->string('emp_emp',45);
            $table->date('fec_reg');
            $table->date('fec_auto');
            $table->string('riesgo',45);
            $table->integer('id_vend')->nullable();
            $table->string('jl',100);
            $table->string('rp',145);
            $table->string('nom_tes',145);
            $table->string('num_tel_tes',45);
            $table->string('num_mail_tes',145);
            $table->integer('vendedor')->nullable();            
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
        Schema::dropIfExists('usr_app_cliente');
    }
}
