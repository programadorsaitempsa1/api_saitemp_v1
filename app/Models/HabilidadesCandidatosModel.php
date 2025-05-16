<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HabilidadesCandidatosModel extends Model
{
    protected $table = 'usr_app_skills_candidatos_c';
    protected $dateFormat = 'd-m-Y H:i:s';
}