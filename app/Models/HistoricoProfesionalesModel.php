<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HistoricoProfesionalesModel extends Model
{
    use HasFactory;
    protected $table = 'usr_app_historico_profesionales_dd';
    protected $dateFormat = 'd-m-Y H:i:s';
}