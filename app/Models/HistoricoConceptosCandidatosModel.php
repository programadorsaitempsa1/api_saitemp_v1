<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HistoricoConceptosCandidatosModel extends Model
{
    use HasFactory;
    protected $table = 'usr_app_historico_concepto_candidatos';
    protected $dateFormat = 'd-m-Y H:i:s';
}
