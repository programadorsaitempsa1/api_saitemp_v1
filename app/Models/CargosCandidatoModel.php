<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CargosCandidatoModel extends Model
{
    use HasFactory;
    protected $table = "usr_app_cargos_candidatos";
    protected $fillable = [
        'nombre'
    ];
    protected $dateFormat = 'd-m-Y H:i:s';
}