<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class SalidaNoConformeseiya extends Model
{
    use HasFactory;

    protected $table = "usr_app_salida_n_conforme_seiya";

    protected $dateFormat = 'Y-d-m H:i:s';
}
