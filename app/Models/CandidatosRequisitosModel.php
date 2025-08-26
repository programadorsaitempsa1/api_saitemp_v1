<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CandidatosRequisitosModel extends Model
{
    use HasFactory;
    protected $table = 'usr_app_cumple_requisitos_candidatos';
    protected $dateFormat = 'd-m-Y H:i:s';
}