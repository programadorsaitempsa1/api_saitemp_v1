<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cargos2TareaAltoRiesgo extends Model
{
    use HasFactory;

    protected $table = "usr_app_cargos2_tareas_alto_riesgo";

    protected $dateFormat = 'd-m-Y H:i:s';
}
