<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HistoricoConsultasApiModel extends Model
{
    use HasFactory;
    protected $table = 'usr_app_historico_masivo_pila';
    protected $dateFormat = 'd-m-Y H:i:s';
}