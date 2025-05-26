<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConceptosMedicosModel extends Model
{
    use HasFactory;
    protected $table = 'usr_app_conceptos_medicos';
    protected $dateFormat = 'd-m-Y H:i:s';
}
