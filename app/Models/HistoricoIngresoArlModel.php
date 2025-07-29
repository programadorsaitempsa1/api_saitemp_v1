<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HistoricoIngresoArlModel extends Model
{
    use HasFactory;
    protected $table = 'usr_app_historico_ingeso_arl';
    protected $dateFormat = 'd-m-Y H:i:s';
}
